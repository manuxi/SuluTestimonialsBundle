<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Domain\Event;

class TestimonialRestoredEvent extends AbstractTestimonialEvent
{
    public function getEventType(): string
    {
        return 'restored';
    }
}
