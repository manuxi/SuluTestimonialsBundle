<?php

// TestimonialsBundle/Handler/TestimonialBulkActionHandler.php

namespace Manuxi\SuluTestimonialsBundle\Handler;

use Manuxi\SuluTestimonialsBundle\Entity\Models\TestimonialModel;
use Symfony\Component\HttpFoundation\Request;

class TestimonialBulkActionHandler
{
    public function __construct(
        private readonly TestimonialModel $testimonialModel,
    ) {
    }

    public function supports(string $resourceKey, string $action): bool
    {
        return 'testimonials' === $resourceKey
            && in_array($action, ['publish', 'unpublish']);
    }

    public function handle(string $action, array $ids, Request $request): array
    {
        return match ($action) {
            'publish' => $this->testimonialModel->publishBulk($ids, $request),
            'unpublish' => $this->testimonialModel->unpublishBulk($ids, $request),
            default => [],
        };
    }
}
