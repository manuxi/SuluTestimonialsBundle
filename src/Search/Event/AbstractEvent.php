<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Search\Event;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

abstract class AbstractEvent extends SymfonyEvent
{
    public function __construct(public Testimonial $entity) {}

    public function getEntity(): Testimonial
    {
        return $this->entity;
    }
}