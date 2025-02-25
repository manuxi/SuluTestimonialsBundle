<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Domain\Event;

class TestimonialPublishedEvent extends AbstractTestimonialEvent
{
    public function getEventType(): string
    {
        return 'published';
    }
}
