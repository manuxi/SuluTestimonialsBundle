<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Routing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\CacheLifetimeMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TemplateMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderRegistry;
use Sulu\Bundle\HttpCacheBundle\CacheLifetime\CacheLifetimeRequestStore;
use Sulu\Bundle\HttpCacheBundle\CacheLifetime\CacheLifetimeResolverInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\TemplateInterface;
use Sulu\Route\Application\Routing\Matcher\RouteDefaultsProviderInterface;
use Sulu\Route\Domain\Model\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TestimonialsRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly MetadataProviderRegistry $metadataProviderRegistry,
        private readonly CacheLifetimeResolverInterface $cacheLifetimeResolver,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaults(Route $route): array
    {
        $id = $route->getResourceId();
        $locale = $route->getLocale();

        $dimensionContent = $this->loadEntity($id, $locale);

        if (null === $dimensionContent) {
            throw new NotFoundHttpException(\sprintf('No content found for id "%s" and locale "%s"', $id, $locale));
        }

        $contentLocale = $dimensionContent->getLocale();
        if (!$contentLocale) {
            throw new NotFoundHttpException(\sprintf('No content found for id "%s" and locale "%s"', $id, $locale));
        }

        $templateKey = $dimensionContent->getTemplateKey();
        if (!$templateKey) {
            throw new NotFoundHttpException(\sprintf('No template found for id "%s" and locale "%s"', $id, $locale));
        }

        $templateMetadata = $this->resolveTemplateMetadata($templateKey, $contentLocale);

        $attributes = [
            'object' => $dimensionContent,
            'view' => $templateMetadata->getView(),
            '_controller' => $templateMetadata->getController(),
        ];

        $cacheLifetime = $this->getCacheLifetime($templateMetadata);
        if ($cacheLifetime) {
            $attributes[CacheLifetimeRequestStore::ATTRIBUTE_KEY] = $cacheLifetime;
        }

        return $attributes;
    }

    private function loadEntity(string $id, string $locale): ?TestimonialDimensionContent
    {
        try {
            /** @var Testimonial $testimonial */
            $testimonial = $this->entityManager->createQueryBuilder()
                ->select('entity')
                ->from(Testimonial::class, 'entity')
                ->leftJoin('entity.dimensionContents', 'dimensionContent')
                ->addSelect('dimensionContent')
                ->where('entity = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            return null;
        }

        try {
            $resolvedDimensionContent = $this->contentAggregator->aggregate(
                $testimonial,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                ]
            );

            if (!$resolvedDimensionContent instanceof TestimonialDimensionContent) {
                return null;
            }

            return $resolvedDimensionContent;
        } catch (ContentNotFoundException) {
            return null;
        }
    }

    private function getCacheLifetime(TemplateMetadata $templateMetadata): ?int
    {
        $cacheLifetime = $templateMetadata->getCacheLifetime();
        if (!$cacheLifetime instanceof CacheLifetimeMetadata) {
            return null;
        }

        $cacheLifeTimeType = $cacheLifetime->getType();
        $cacheLifeTimeValue = $cacheLifetime->getValue();

        if (!$this->cacheLifetimeResolver->supports($cacheLifeTimeType, $cacheLifeTimeValue)) {
            return null;
        }

        return $this->cacheLifetimeResolver->resolve($cacheLifeTimeType, $cacheLifeTimeValue);
    }

    private function resolveTemplateMetadata(string $templateKey, string $locale): TemplateMetadata
    {
        $typedMetadata = $this->metadataProviderRegistry->getMetadataProvider('form')
            ->getMetadata(Testimonial::TEMPLATE_TYPE, $locale, []);

        if (!$typedMetadata instanceof TypedFormMetadata) {
            throw new \RuntimeException(\sprintf('Could not find metadata "%s" of type "%s".', 'form', Testimonial::TEMPLATE_TYPE));
        }

        $metadata = $typedMetadata->getForms()[$templateKey] ?? null;

        if (!$metadata instanceof FormMetadata) {
            throw new \RuntimeException(\sprintf('Could not find form metadata "%s" of type "%s".', $templateKey, Testimonial::TEMPLATE_TYPE));
        }

        $templateMetadata = $metadata->getTemplate();

        if (!$templateMetadata instanceof TemplateMetadata) {
            throw new \RuntimeException(\sprintf('Could not find template metadata "%s" of type "%s".', $templateKey, Testimonial::TEMPLATE_TYPE));
        }

        return $templateMetadata;
    }
}
