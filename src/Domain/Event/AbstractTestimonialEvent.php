<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Domain\Event;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

abstract class AbstractTestimonialEvent extends DomainEvent
{
    private Testimonial $testimonial;
    private array $payload = [];

    public function __construct(Testimonial $testimonial, array $payload)
    {
        parent::__construct();
        $this->testimonial = $testimonial;
        $this->payload = $payload;
    }

    public function getTestimonial(): Testimonial
    {
        return $this->testimonial;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getResourceKey(): string
    {
        return Testimonial::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->testimonial->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->testimonial->getTitle();
    }

    public function getResourceSecurityContext(): ?string
    {
        return Testimonial::SECURITY_CONTEXT;
    }
}
