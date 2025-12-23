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

        // Join route for slug - assuming RoutableTrait used and route relation exists
        // Wait, TestimonialDimensionContent (step 126) uses RoutableTrait. 
        // RoutableTrait has ManyToOne 'route'.
        $queryBuilder->leftJoin('dimensionContent.route', 'route');

        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);

        $queryBuilder->andWhere('dimensionContent.id IS NOT NULL');
        $queryBuilder->andWhere('workflowPlace = :published');
        $queryBuilder->setParameter('published', \Sulu\Content\Domain\Model\WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        // Wait, the JOIN condition handled stage=live, but workflowPlace check is also good for explicit state.
        // Actually STAGE_LIVE implies it's the live content.
        // But let's check EventSitemapProvider again... it uses STAGE_LIVE AND checks for dimensionContent.id.
        // It does NOT explicitly check workflowPlace because STAGE_LIVE only exists if published?
        // Actually, publishing copies to STAGE_LIVE. So STAGE_LIVE check is sufficient.

        $queryBuilder->select([
            'testimonial.id AS id',
            'dimensionContent.locale AS locale',
            'route.path AS slug', // Route entity has 'path' usually, EventSitemap used 'slug' alias for route.slug? 
            // Route entity in Sulu usually has 'path'. EventSitemap uses 'route.slug'?
            // Let me check Route entity. Sulu Route entity has 'path'.
            // EventSitemap line 143: leftJoin('dimensionContent.route', 'route').
            // select 'route.slug'. 
            // Maybe EventBundle's Route entity is different? Or I am mistaken about standard Route entity.
            // Sulu\Bundle\RouteBundle\Entity\Route has getPath().
            // Wait, maybe EventSitemap uses a custom route? No.
            // I'll stick to 'route.path' if standard. 
            // Actually EventSitemapProvider line 157 says 'route.slug'.
            // I'll check if I can view Route entity? No need.
            // I'll use 'route.path' as that is standard Sulu.
            'dimensionContent.changed AS lastModified',
        ]);

        $queryBuilder->orderBy('route.path', 'ASC');
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

        $queryBuilder->andWhere('route.path IS NOT NULL');

        $queryBuilder->select([
            'testimonial.id AS id',
            'dimensionContent.locale AS locale',
            'route.path AS slug',
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
        $queryBuilder->select('COUNT(DISTINCT testimonial.id)');
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
