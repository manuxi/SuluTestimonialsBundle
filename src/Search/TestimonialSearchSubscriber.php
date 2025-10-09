<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Search;

use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialRemovedEvent;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialSavedEvent;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialUnpublishedEvent;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestimonialSearchSubscriber implements EventSubscriberInterface
{

    public function __construct(private readonly SearchManagerInterface $searchManager) {}

    public static function getSubscribedEvents(): array
    {
        return [
            TestimonialPublishedEvent::class => 'onPublished',
            TestimonialUnpublishedEvent::class => 'onUnpublished',
            TestimonialSavedEvent::class => 'onSaved',
            TestimonialRemovedEvent::class => 'onRemoved',
        ];
    }

    public function onPublished(TestimonialPublishedEvent $event): void
    {
        $entity = $event->getEntity();
        if($entity->isPublished()) {
            $this->searchManager->index($entity);
        }
    }

    public function onUnpublished(TestimonialUnpublishedEvent $event): void
    {
        $this->searchManager->deindex($event->getEntity());
    }

    public function onSaved(TestimonialSavedEvent $event): void
    {
        $entity = $event->getEntity();
        if($entity->isPublished()) {
            $this->searchManager->index($entity);
        } else {
            $this->searchManager->deindex($entity);
        }
    }

    public function onRemoved(TestimonialRemovedEvent $event): void
    {
        $this->searchManager->deindex($event->getEntity());
    }
}