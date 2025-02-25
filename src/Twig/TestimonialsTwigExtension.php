<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Twig;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TestimonialsTwigExtension extends AbstractExtension
{
    private TestimonialRepository $testimonialRepository;

    public function __construct(TestimonialRepository $testimonialRepository)
    {
        $this->testimonialRepository = $testimonialRepository;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sulu_resolve_testimonial', [$this, 'resolveTestimonial']),
            new TwigFunction('sulu_get_testimonials', [$this, 'getTestimonials'])
        ];
    }

    public function resolveTestimonial(int $id, string $locale = 'en'): ?Testimonial
    {
        $testimonial = $this->testimonialRepository->findById($id, $locale);

        return $testimonial ?? null;
    }

    public function getTestimonials(int $limit = 100, $locale = 'en')
    {
        return $this->testimonialRepository->findByFilters([], 0, $limit, $limit, $locale);
    }
}