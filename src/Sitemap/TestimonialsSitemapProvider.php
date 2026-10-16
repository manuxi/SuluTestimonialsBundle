<?php
declare(strict_types=1);
namespace Manuxi\SuluTestimonialsBundle\Sitemap;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapAlternateLink;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
class TestimonialsSitemapProvider implements SitemapProviderInterface
{
    public const PAGE_SIZE = 10000;
    private EntityRepository $entityRepository;
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WebspaceManagerInterface $webspaceManager,
        private string $environment = 'prod', // Default environment, usually injected
    ) {
        $this->entityRepository = $this->entityManager->getRepository(Testimonial::class);
    }
    public function build($page, $scheme, $host)
    {
        $locale = $this->getLocaleByHost($host);
        if (!$locale) {
            return [];
        }
        $offset = ($page - 1) * self::PAGE_SIZE;
        $testimonials = $this->findTestimonials($locale, self::PAGE_SIZE, $offset);
        $alternateRoutes = $this->getAlternateRoutes($locale);
        $result = [];
        foreach ($testimonials as $testimonialData) {
            $id = (string) $testimonialData['id'];
            $testimonialLocale = $testimonialData['locale'];
            $slug = $testimonialData['slug'];
            $lastModified = $testimonialData['lastModified'];
            if (empty($slug)) {
                continue;
            }
            $sitemapUrl = new SitemapUrl(
                $scheme . '://' . $host . $slug,
                $testimonialLocale,
                $testimonialLocale,
                $lastModified
            );
            if (isset($alternateRoutes[$id])) {
                foreach ($alternateRoutes[$id] as $alternateLocale => $alternateSlug) {
                    if ($alternateLocale !== $testimonialLocale && !empty($alternateSlug)) {
                        $sitemapUrl->addAlternateLink(
                            new SitemapAlternateLink(
                                $scheme . '://' . $host . $alternateSlug,
                                $alternateLocale
                            )
                        );
                    }
                }
            }
            $result[] = $sitemapUrl;
        }
        return $result;
    }
    public function createSitemap($scheme, $host)
    {
        return new Sitemap($this->getAlias(), $this->getMaxPage($scheme, $host));
    }
    public function getAlias()
    {
        return 'testimonials';
    }
    public function getMaxPage($scheme, $host)
    {
        $locale = $this->getLocaleByHost($host);
        if (!$locale) {
            return 0;
        }
        return (int) ceil($this->countTestimonials($locale) / self::PAGE_SIZE);
    }
    private function getLocaleByHost($host)
    {
        $portalInformations = $this->webspaceManager->findPortalInformationsByHostIncludingSubdomains(
            $host,
            $this->environment
        );
        if (0 === \count($portalInformations)) {
            return null;
        }
        return \reset($portalInformations)->getLocale();
    }
    private function findTestimonials(string $locale, int $limit, int $offset): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('testimonial');
        $queryBuilder->leftJoin(
            'testimonial.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale
             AND dimensionContent.stage = :stage
             AND dimensionContent.version = :version
             AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)'
        );
        // the live stage only exists for published content (workflowPlace is NULL on live rows from Sulu 3)
        $queryBuilder->leftJoin('dimensionContent.route', 'route');
        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);
        $queryBuilder->andWhere('dimensionContent.id IS NOT NULL');
        $queryBuilder->select([
            'testimonial.uuid AS id',
            'dimensionContent.locale AS locale',
            'route.slug AS slug',
            'dimensionContent.changed AS lastModified',
        ]);
        $queryBuilder->orderBy('route.slug', 'ASC');
        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);
        return $queryBuilder->getQuery()->getResult();
    }
    private function getAlternateRoutes(string $currentLocale): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('testimonial');
        $queryBuilder->leftJoin(
            'testimonial.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale IS NOT NULL
              AND dimensionContent.stage = :stage
              AND dimensionContent.version = :version
              AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)'
        );
        $queryBuilder->leftJoin('dimensionContent.route', 'route');
        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);
        $queryBuilder->andWhere('route.slug IS NOT NULL');
        $queryBuilder->select([
            'testimonial.uuid AS id',
            'dimensionContent.locale AS locale',
            'route.slug AS slug',
        ]);
        $result = [];
        foreach ($queryBuilder->getQuery()->getResult() as $row) {
            $id = (string) $row['id'];
            $locale = $row['locale'];
            $slug = $row['slug'];
            if (!isset($result[$id])) {
                $result[$id] = [];
            }
            $result[$id][$locale] = $slug;
        }
        return $result;
    }
    private function countTestimonials(string $locale): int
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('testimonial');
        $queryBuilder->select('COUNT(DISTINCT testimonial.uuid)');
        $queryBuilder->leftJoin(
            'testimonial.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale
             AND dimensionContent.stage = :stage
             AND dimensionContent.version = :version
             AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)'
        );
        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);
        $queryBuilder->andWhere('dimensionContent.id IS NOT NULL');
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
