<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Application\Mapper;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;

interface TestimonialMapperInterface
{
    public function mapTestimonialData(Testimonial $testimonial, array $data): void;
}