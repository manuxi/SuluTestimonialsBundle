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

        $intIds = \array_map('intval', $ids);

        $stage = $params['stage'] ?? DimensionContentInterface::STAGE_LIVE;
        $result = $this->testimonialRepository->findByIds($intIds, $locale, $stage);

        $mappedResult = [];
        foreach ($result as $event) {
            $mappedResult[(string) $event->getId()] = $event;
        }

        return $mappedResult;
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