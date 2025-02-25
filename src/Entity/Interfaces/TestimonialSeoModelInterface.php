<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Interfaces;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialSeo;
use Symfony\Component\HttpFoundation\Request;

interface TestimonialSeoModelInterface
{
    public function updateTestimonialSeo(TestimonialSeo $testimonialSeo, Request $request): TestimonialSeo;
}
