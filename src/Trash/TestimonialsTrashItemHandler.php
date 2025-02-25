<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Trash;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Admin\TestimonialsAdmin;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialRestoredEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TestimonialsTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;
    private EntityManagerInterface $entityManager;
    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface   $trashItemRepository,
        EntityManagerInterface         $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface  $domainEventCollector
    )
    {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return Testimonial::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        $image = $resource->getImage();
        $contact = $resource->getContact();

        $data = [
            "title" => $resource->getTitle(),
            "text" => $resource->getText(),
            "date" => $resource->getDate(),
            "rating" => $resource->getRating(),
            "source" => $resource->getSource(),
            "slug" => $resource->getRoutePath(),
            "published" => $resource->isPublished(),
            "publishedAt" => $resource->getPublishedAt(),
            "ext" => $resource->getExt(),
            "locale" => $resource->getLocale(),
            "imageId" => $image ? $image->getId() : null,
            "contactId" => $contact ? $contact->getId() : null,
            "url" => $resource->getUrl(),
            "showContact" => $resource->getShowContact(),
            "showOrganisation" => $resource->getShowOrganisation(),
            "showDate" => $resource->getShowDate(),
            "authored" => $resource->getAuthored(),
            "author" => $resource->getAuthor(),

        ];
        return $this->trashItemRepository->create(
            Testimonial::RESOURCE_KEY,
            (string)$resource->getId(),
            $resource->getTitle(),
            $data,
            null,
            $options,
            Testimonial::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {

        $data = $trashItem->getRestoreData();
        $testimonialId = (int)$trashItem->getResourceId();
        $testimonial = new Testimonial();
        $testimonial->setLocale($data['locale']);
        $testimonial->setTitle($data['title']);
        $testimonial->setText($data['text']);
        $testimonial->setDate($data['date'] ? new DateTime($data['date']) : new DateTime());
        $testimonial->setRating($data['rating']);
        $testimonial->setSource($data['source']);
        $testimonial->setRoutePath($data['slug']);
        $testimonial->setExt($data['ext']);
        $testimonial->setPublished($data['published']);
        $testimonial->setPublishedAt($data['publishedAt'] ? new DateTime($data['publishedAt']['date']) : null);
        $testimonial->setShowContact($data['showContact']);
        $testimonial->setShowOrganisation($data['showOrganisation']);
        $testimonial->setShowDate($data['showDate']);
        $testimonial->setAuthored($data['authored'] ? new DateTime($data['authored']['date']) : new DateTime());

        if ($data['author']) {
            $testimonial->setAuthor($this->entityManager->find(ContactInterface::class, $data['author']));
        }

        if($data['url']) {
            $testimonial->setUrl($data['url']);
        }

        if($data['imageId']){
            $testimonial->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        }

        if($data['contactId']){
            $testimonial->setContact($this->entityManager->find(ContactInterface::class, $data['contactId']));
        }

        $this->domainEventCollector->collect(
            new TestimonialRestoredEvent($testimonial, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($testimonial, $testimonialId);
        $this->createRoute($this->entityManager, $testimonialId, $data['locale'], $testimonial->getRoutePath(), Testimonial::class);
        $this->entityManager->flush();
        return $testimonial;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $locale, string $slug, string $class)
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale($locale);
        $route->setEntityClass($class);
        $route->setEntityId($id);
        $route->setHistory(0);
        $route->setCreated(new DateTime());
        $route->setChanged(new DateTime());
        $manager->persist($route);
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            TestimonialsAdmin::EDIT_FORM_VIEW,
            ['id' => 'id']
        );
    }
}
