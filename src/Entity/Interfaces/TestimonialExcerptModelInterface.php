<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Interfaces;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialExcerpt;
use Symfony\Component\HttpFoundation\Request;

interface TestimonialExcerptModelInterface
{
    public function updateTestimonialExcerpt(TestimonialExcerpt $testimonialExcerpt, Request $request): TestimonialExcerpt;
}
