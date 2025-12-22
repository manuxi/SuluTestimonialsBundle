<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\Type;

use Manuxi\SuluTestimonialsBundle\Repository\TestimonialDimensionContentRepository;
use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class TestimonialsSelectionPropertyResolver implements PropertyResolverInterface
{
    public function __construct(
        private readonly TestimonialDimensionContentRepository $testimonialDimensionContentRepository
    ) {
    }

    public static function getType(): string
    {
        return 'testimonials_selection';
    }

    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (empty($data)) {
            return ContentView::create([], ['ids' => []]);
        }

        // data for multiple selection is usually `['ids' => [...]]` or just `[...]`?
        // Standard List Selections usually return `['ids' => [1, 2, 3]]`.

        $ids = is_array($data) ? ($data['ids'] ?? $data) : [];
        if (!is_array($ids)) {
            $ids = [];
        }

        if (empty($ids)) {
            return ContentView::create([], ['ids' => []]);
        }

        // Fetch entities by Testimonial IDs and Locale
        $entities = $this->testimonialDimensionContentRepository->findBy([
            'testimonial' => $ids,
            'locale' => $locale
        ]);

        // Sort entities by IDs order if needed? (Sulu usually expects robust handling).
        // Check standard findBy behavior (no order guarantee).

        return ContentView::create($entities, ['ids' => $ids]);
    }
}
