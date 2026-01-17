<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Twig;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class TestimonialsTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private TestimonialRepository $testimonialRepository,
        private int $ratingMaxValue = 5,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'testimonials_rating_max_value' => $this->ratingMaxValue,
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_resolve_testimonial', [$this, 'resolveTestimonial']),
            new TwigFunction('sulu_get_testimonials', [$this, 'getTestimonials']),
        ];
    }

    public function resolveTestimonial(int $id, string $locale = 'en'): ?Testimonial
    {
        $testimonial = $this->testimonialRepository->findById($id, $locale);

        return $testimonial ?? null;
    }

    public function getTestimonials(int $limit = 100, string $locale = 'en'): array
    {
        return $this->testimonialRepository->findByFilters([], 0, $limit, $limit, $locale);
    }
}