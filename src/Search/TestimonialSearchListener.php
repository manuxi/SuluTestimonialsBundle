<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Search;

use CmsIg\Seal\EngineInterface;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialCreatedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialModifiedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialRemovedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialUnpublishedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestimonialSearchListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EngineInterface $engine,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TestimonialCreatedEvent::class => 'onCreatedOrModified',
            TestimonialModifiedEvent::class => 'onCreatedOrModified',
            TestimonialPublishedEvent::class => 'onPublished',
            TestimonialUnpublishedEvent::class => 'onUnpublished',
            TestimonialRemovedEvent::class => 'onRemoved',
        ];
    }

    public function onCreatedOrModified(TestimonialCreatedEvent|TestimonialModifiedEvent $domainEvent): void
    {
        $testimonial = $domainEvent->getTestimonial();

        foreach ($this->getLocales() as $locale) {
            /** @var TestimonialDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $testimonial,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            $this->indexForAdmin($testimonial, $dimensionContent, $locale);
        }
    }

    public function onPublished(TestimonialPublishedEvent $domainEvent): void
    {
        $testimonial = $domainEvent->getTestimonial();

        foreach ($this->getLocales() as $locale) {
            /** @var TestimonialDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $testimonial,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            $this->indexForAdmin($testimonial, $dimensionContent, $locale);
            $this->indexForWebsite($testimonial, $dimensionContent, $locale);
        }
    }

    public function onUnpublished(TestimonialUnpublishedEvent $domainEvent): void
    {
        $testimonial = $domainEvent->getTestimonial();

        foreach ($this->getLocales() as $locale) {
            /** @var TestimonialDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $testimonial,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            $this->indexForAdmin($testimonial, $dimensionContent, $locale);

            $documentId = $this->getDocumentId($testimonial, $locale);
            $this->engine->deleteDocument('website', $documentId);
        }
    }

    public function onRemoved(TestimonialRemovedEvent $domainEvent): void
    {
        foreach ($this->getLocales() as $locale) {
            $documentId = 'testimonial-' . $domainEvent->getResourceId() . '-' . $locale;
            $this->engine->deleteDocument('admin', $documentId . '-draft');
            $this->engine->deleteDocument('website', $documentId);
        }
    }

    private function indexForAdmin(Testimonial $testimonial, TestimonialDimensionContent $dimensionContent, string $locale): void
    {
        $content = array_filter([
            $dimensionContent->getText(),
            $dimensionContent->getSource(),
        ]);

        $contact = $dimensionContent->getContact();
        $contactName = $contact ? trim($contact->getFirstName() . ' ' . $contact->getLastName()) : null;

        $this->engine->saveDocument('admin', [
            'id' => $this->getDocumentId($testimonial, $locale) . '-draft',
            'resourceKey' => Testimonial::RESOURCE_KEY,
            'resourceId' => (string) $testimonial->getId(),
            'locale' => $locale,
            'securityContext' => Testimonial::SECURITY_CONTEXT,
            'title' => $dimensionContent->getTitle() ?? '',
            'content' => $content,
            'contact' => $contactName,
            'rating' => $dimensionContent->getRating(),
            'mediaId' => $dimensionContent->getImage()?->getId(),
            'changedAt' => $dimensionContent->getChanged()?->format('c'),
            'createdAt' => $dimensionContent->getCreated()?->format('c'),
            'published' => WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace(),
        ]);
    }

    private function indexForWebsite(Testimonial $testimonial, TestimonialDimensionContent $dimensionContent, string $locale): void
    {
        $content = array_filter([
            $dimensionContent->getText(),
            $dimensionContent->getSource(),
        ]);

        $contact = $dimensionContent->getContact();
        $contactName = $contact ? trim($contact->getFirstName() . ' ' . $contact->getLastName()) : null;

        $url = $dimensionContent->getRoute()?->getPath();

        $this->engine->saveDocument('website', [
            'id' => $this->getDocumentId($testimonial, $locale),
            'resourceKey' => Testimonial::RESOURCE_KEY,
            'resourceId' => (string) $testimonial->getId(),
            'locale' => $locale,
            'title' => $dimensionContent->getTitle() ?? '',
            'content' => $content,
            'contact' => $contactName,
            'rating' => $dimensionContent->getRating(),
            'url' => $url,
            'mediaId' => $dimensionContent->getImage()?->getId(),
        ]);
    }

    private function getDocumentId(Testimonial $testimonial, string $locale): string
    {
        return 'testimonial-' . $testimonial->getId() . '-' . $locale;
    }

    private function getLocales(): array
    {
        $locales = [];
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            foreach ($webspace->getAllLocalizations() as $localization) {
                $locales[$localization->getLocale()] = true;
            }
        }

        return array_keys($locales);
    }
}