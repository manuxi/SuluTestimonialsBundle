<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Admin\TestimonialsAdmin;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialRestoredEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TestimonialsTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    public function __construct(
        private readonly TrashItemRepositoryInterface $trashItemRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public static function getResourceKey(): string
    {
        return Testimonial::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        /* @var Testimonial $resource */
        $locale = $options['locale'] ?? null;

        // Helper to get dimension content would be nice, but we can do it inline or via entity if helper existed.
        // We assume locale is present for content entities usually.
        $dimensionContent = $this->getDimensionContent($resource, $locale);

        $image = $dimensionContent->getImage();
        $contact = $dimensionContent->getContact();

        $data = [
            'title' => $dimensionContent->getTitle(),
            'text' => $dimensionContent->getText(),
            'date' => $dimensionContent->getDate(),
            'rating' => $dimensionContent->getRating(),
            'source' => $dimensionContent->getSource(),
            //'slug' => $resource->getRoutePath(), // Route path usually on DimensionContent if Routable
            'published' => $dimensionContent->getWorkflowPlace() === WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
            'publishedAt' => $dimensionContent->getWorkflowPublished(),
            //'ext' => $resource->getExt(), // Ext was mixed bag. Dimension Content doesn't have it by default unless added.
            'locale' => $locale,
            'imageId' => $image?->getId(),
            'contactId' => $contact ? $contact->getId() : null,
            'url' => $dimensionContent->getWebsite(),
            'showContact' => $dimensionContent->getShowContact(),
            'showOrganisation' => $dimensionContent->getShowOrganisation(),
            'showDate' => $dimensionContent->getShowDate(),
            // Authored? DimensionContent usually has AuthorTrait
            'authored' => $dimensionContent->getCreated(), // approximation
            'author' => $dimensionContent->getCreator()?->getContact()?->getId(),
        ];

        $restoreType = isset($options['locale']) ? 'translation' : null;

        return $this->trashItemRepository->create(
            Testimonial::RESOURCE_KEY,
            (string) $resource->getId(),
            (string) $data['title'],
            $data,
            $restoreType,
            $options,
            Testimonial::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $testimonialId = (int) $trashItem->getResourceId();

        $testimonial = $this->entityManager->find(Testimonial::class, $testimonialId);
        if (!$testimonial) {
            $testimonial = new Testimonial();
            // id restoration is tricky if we create new. usually we just create new and let standard persist handle id if not set.
            // But DoctrineRestoreHelper persists with ID?
            // If we restore completely deleted item, we create new.
            // We must create DimensionContent.
        }

        // This restore logic is complex for ContentRichEntity because we must create/update DimensionContent.
        // For simplicity, we assume we restore into a new Testimonial if deleted.

        if (!$testimonial) {
            $testimonial = new Testimonial();
        }

        $locale = $data['locale'];
        // We find or create dimension content for this locale.
        $dimensionContent = $this->getDimensionContent($testimonial, $locale);
        if (!$dimensionContent) {
            $dimensionContent = new \Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent($testimonial);
            $dimensionContent->setLocale($locale);
            $testimonial->addDimensionContent($dimensionContent);
        }

        $dimensionContent->setTitle($data['title']);
        $dimensionContent->setText($data['text']);
        $dimensionContent->setDate($data['date'] ? (\is_string($data['date']) ? new \DateTime($data['date']) : $data['date']) : new \DateTime());
        $dimensionContent->setRating($data['rating']);
        $dimensionContent->setSource($data['source']);
        //$testimonial->setRoutePath($data['slug']);
        //$testimonial->setExt($data['ext']);

        if ($data['published']) {
            $dimensionContent->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        } else {
            $dimensionContent->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        }

        $dimensionContent->setWorkflowPublished($data['publishedAt'] ? (\is_string($data['publishedAt']) ? new \DateTime($data['publishedAt']) : (is_array($data['publishedAt']) ? new \DateTime($data['publishedAt']['date']) : null)) : null);

        $dimensionContent->setShowContact($data['showContact']);
        $dimensionContent->setShowOrganisation($data['showOrganisation']);
        $dimensionContent->setShowDate($data['showDate']);
        //$testimonial->setAuthored($data['authored'] ? new \DateTime($data['authored']['date']) : new \DateTime());

        if ($data['url']) {
            $dimensionContent->setWebsite($data['url']);
        }

        if ($data['imageId']) {
            $dimensionContent->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        }

        if ($data['contactId']) {
            $dimensionContent->setContact($this->entityManager->find(ContactInterface::class, $data['contactId']));
        }

        $this->domainEventCollector->collect(
            new TestimonialRestoredEvent($testimonial, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($testimonial, $testimonialId);
        // $this->createRoute($this->entityManager, $testimonialId, $data['locale'], $testimonial->getRoutePath(), Testimonial::class);
        $this->entityManager->flush();

        return $testimonial;
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            TestimonialsAdmin::EDIT_FORM_VIEW,
            ['id' => 'id']
        );
    }

    private function getDimensionContent(Testimonial $testimonial, ?string $locale): ?\Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent
    {
        if (!$locale)
            return null;
        foreach ($testimonial->getDimensionContents() as $dc) {
            if ($dc->getLocale() === $locale) { // && check stage? usually we take one.
                return $dc;
            }
        }
        return null;
    }
}
