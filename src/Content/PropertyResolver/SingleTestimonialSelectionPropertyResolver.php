<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\PropertyResolver;

use Manuxi\SuluTestimonialsBundle\Content\ResourceLoader\TestimonialResourceLoader;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class SingleTestimonialSelectionPropertyResolver implements PropertyResolverInterface
{
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (null === $data || !\is_int($data)) {
            return ContentView::create(null, ['id' => null, ...$params]);
        }

        return ContentView::createResolvableWithReferences(
            id: (string) $data,
            resourceLoaderKey: TestimonialResourceLoader::getKey(),
            resourceKey: Testimonial::RESOURCE_KEY,
            view: ['id' => $data, ...$params],
            priority: 150,
            metadata: ['properties' => $params['properties'] ?? null]
        );
    }

    public static function getType(): string
    {
        return 'single_testimonial_selection';
    }
}
