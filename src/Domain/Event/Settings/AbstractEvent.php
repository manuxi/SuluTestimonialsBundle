<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Domain\Event\Settings;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialsSettings;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

abstract class AbstractEvent extends DomainEvent
{
    private TestimonialsSettings $entity;
    private array $payload = [];

    public function __construct(TestimonialsSettings $entity)
    {
        parent::__construct();
        $this->entity = $entity;
    }

    public function getEvent(): TestimonialsSettings
    {
        return $this->entity;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getResourceKey(): string
    {
        return TestimonialsSettings::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->entity->getId();
    }

    public function getResourceTitle(): ?string
    {
        return "Testimonials Settings";
    }

    public function getResourceSecurityContext(): ?string
    {
        return TestimonialsSettings::SECURITY_CONTEXT;
    }
}
