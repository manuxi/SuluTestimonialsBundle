<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\Type;

use Manuxi\SuluTestimonialsBundle\Repository\TestimonialDimensionContentRepository;
use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class SingleTestimonialSelectionPropertyResolver implements PropertyResolverInterface
{
    public function __construct(
        private readonly TestimonialDimensionContentRepository $testimonialDimensionContentRepository
    ) {
    }

    public static function getType(): string
    {
        return 'single_testimonial_selection';
    }

    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (empty($data)) {
            return ContentView::create(null, ['id' => null]);
        }

        // data is likely just an ID or array with id?
        // Standard Single Selection usually stores just the ID or UUID.
        // But some store {"id": ...}

        $id = is_array($data) ? ($data['id'] ?? null) : $data;

        if (!$id) {
            return ContentView::create(null, ['id' => null]);
        }

        $entity = $this->testimonialDimensionContentRepository->load((string) $id, ['locale' => $locale]);

        return ContentView::create($entity, ['id' => $id]);
    }
}
