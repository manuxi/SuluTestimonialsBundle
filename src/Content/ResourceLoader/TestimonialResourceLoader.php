<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\ResourceLoader;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Content\Application\ResourceLoader\Loader\ResourceLoaderInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class TestimonialResourceLoader implements ResourceLoaderInterface
{
    public const RESOURCE_LOADER_KEY = 'testimonials';

    public function __construct(
        private TestimonialRepository $testimonialRepository,
    ) {
    }

    /**
     * @param string[] $ids
     * @param array<string, mixed> $params
     * @return array<string, Testimonial>
     */
    public function load(array $ids, ?string $locale, array $params = []): array
    {
        if (empty($ids)) {
            return [];
        }



        $stage = $params['stage'] ?? DimensionContentInterface::STAGE_LIVE;
        // Without a locale nothing can be resolved. The repository also returns entries that have no content in this
        // locale and stage (for example after unpublishing: only the unlocalized content is left); resolving those
        // ends in an error, so they are skipped like unpublished articles.
        if (null === $locale) {
            return [];
        }

        $result = array_filter(
            $this->testimonialRepository->findByUuids($ids, $locale, $stage),
            fn ($testimonial) => $this->hasContentFor($testimonial, $locale, $stage),
        );

        $mappedResult = [];
        foreach ($result as $event) {
            $mappedResult[$event->getId()] = $event;
        }

        return $mappedResult;
    }

    private function hasContentFor(object $entity, string $locale, string $stage): bool
    {
        foreach ($entity->getDimensionContents() as $dimensionContent) {
            if ($dimensionContent->getStage() === $stage && $dimensionContent->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    public static function getKey(): string
    {
        return self::RESOURCE_LOADER_KEY;
    }

    public static function getResourceKey(): string
    {
        return Testimonial::RESOURCE_KEY;
    }
}