<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\PropertyResolver;

use Manuxi\SuluTestimonialsBundle\Content\ResourceLoader\TestimonialResourceLoader;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class TestimonialSelectionPropertyResolver implements PropertyResolverInterface
{
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (!\is_array($data) || 0 === \count($data)) {
            return ContentView::create([], ['ids' => [], ...$params]);
        }

        // Convert int IDs to strings for ResourceLoader
        $stringIds = array_map('strval', $data);

        return ContentView::createResolvablesWithReferences(
            ids: $stringIds,
            resourceLoaderKey: TestimonialResourceLoader::getKey(),
            resourceKey: Testimonial::RESOURCE_KEY,
            view: ['ids' => $data, ...$params],
            priority: 150
        );
    }

    public static function getType(): string
    {
        return 'testimonial_selection';
    }
}