<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Component\SmartContent\ItemInterface;

#[Serializer\ExclusionPolicy("all")]
class TestimonialDataItem implements ItemInterface
{

    private Testimonial $entity;

    public function __construct(Testimonial $entity)
    {
        $this->entity = $entity;
    }

    #[Serializer\VirtualProperty]
    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    #[Serializer\VirtualProperty]
    public function getTitle(): string
    {
        return (string) $this->entity->getTitle();
    }

    #[Serializer\VirtualProperty]
    public function getImage(): ?string
    {
        return null;
    }

    #[Serializer\VirtualProperty]
    public function getName(): string
    {
        return (string) $this->entity->getTitle();
    }

    #[Serializer\VirtualProperty]
    public function getText(): string
    {
        return (string) $this->entity->getText();
    }

    #[Serializer\VirtualProperty]
    public function getRating(): ?string
    {
        return (string) $this->entity->getRating();
    }

    public function getResource(): Testimonial
    {
        return $this->entity;
    }
}
