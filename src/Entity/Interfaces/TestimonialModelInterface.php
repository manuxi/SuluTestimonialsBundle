<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Interfaces;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Symfony\Component\HttpFoundation\Request;

interface TestimonialModelInterface
{
    public function get(int $id, Request $request = null): Testimonial;
    public function delete(Testimonial $entity): void;
    public function create(Request $request): Testimonial;
    public function update(int $id, Request $request): Testimonial;
    public function publish(int $id, Request $request): Testimonial;
    public function unpublish(int $id, Request $request): Testimonial;
}
