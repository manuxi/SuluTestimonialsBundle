<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Reference;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Bundle\WebsiteBundle\ReferenceStore\ReferenceStore;

class TestimonialReferenceStore extends ReferenceStore
{
    public static function getKey(): string
    {
        return 'testimonials'; // Or Testimonial::RESOURCE_KEY if it matches
    }
}
