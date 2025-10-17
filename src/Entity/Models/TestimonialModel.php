<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Models;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialCopiedLanguageEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialCreatedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialModifiedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialRemovedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialUnpublishedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\TestimonialModelInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialPublishedEvent as SearchPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialRemovedEvent as SearchRemovedEvent;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialSavedEvent as SearchSavedEvent;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialUnpublishedEvent as SearchUnpublishedEvent;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TestimonialModel implements TestimonialModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly ContactRepository $contactRepository,
        private readonly RouteManagerInterface $routeManager,
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(int $id, ?Request $request = null): Testimonial
    {
        if (null === $request) {
            return $this->findById($id);
        }

        return $this->findByIdAndLocale($id, $request);
    }

    public function delete(Testimonial $entity): void
    {
        $this->domainEventCollector->collect(
            new TestimonialRemovedEvent($entity->getId(), $entity->getTitle() ?? '')
        );
        $this->dispatcher->dispatch(new SearchRemovedEvent($entity));

        $this->removeRoutesForEntity($entity);
        $this->testimonialRepository->remove($entity->getId());
    }

    /**
     * @throws EntityNotFoundException
     */
    public function create(Request $request): Testimonial
    {
        $entity = $this->testimonialRepository->create((string) $this->getLocaleFromRequest($request));
        $entity = $this->mapDataToEntity($entity, $request->request->all());

        $this->domainEventCollector->collect(
            new TestimonialCreatedEvent($entity, $request->request->all())
        );

        // need the id for updateRoutesForEntity(), so we have to persist and flush here
        $entity = $this->testimonialRepository->save($entity);

        $this->updateRoutesForEntity($entity);

        // explicit flush to save routes persisted by updateRoutesForEntity()
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new SearchSavedEvent($entity));

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(int $id, Request $request): Testimonial
    {
        $entity = $this->findByIdAndLocale($id, $request);
        $this->dispatcher->dispatch(new SearchUnpublishedEvent($entity));

        $entity = $this->mapDataToEntity($entity, $request->request->all());
        $entity = $this->mapSettingsToEntity($entity, $request->request->all());

        $this->domainEventCollector->collect(
            new TestimonialModifiedEvent($entity, $request->request->all())
        );

        $entity = $this->testimonialRepository->save($entity);

        $this->updateRoutesForEntity($entity);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new SearchSavedEvent($entity));

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function publish(int $id, Request $request): Testimonial
    {
        $entity = $this->findByIdAndLocale($id, $request);
        $this->dispatcher->dispatch(new SearchUnpublishedEvent($entity));

        $entity->setPublished(true);
        $entity = $this->testimonialRepository->save($entity);

        $this->domainEventCollector->collect(
            new TestimonialPublishedEvent($entity, $request->request->all())
        );
        $this->dispatcher->dispatch(new SearchPublishedEvent($entity));

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function unpublish(int $id, Request $request): Testimonial
    {
        $entity = $this->findByIdAndLocale($id, $request);
        $this->dispatcher->dispatch(new SearchUnpublishedEvent($entity));

        $entity->setPublished(false);
        $entity = $this->testimonialRepository->save($entity);

        $this->domainEventCollector->collect(
            new TestimonialUnpublishedEvent($entity, $request->request->all())
        );
        $this->dispatcher->dispatch(new SearchPublishedEvent($entity));

        return $entity;
    }

    public function copy(int $id, Request $request): Testimonial
    {
        $locale = $this->getLocaleFromRequest($request);

        $entity = $this->findById($id);
        $entity->setLocale($locale);

        $copy = $this->testimonialRepository->create($locale);

        $copy = $entity->copy($copy);
        $copy = $this->testimonialRepository->save($copy);
        $this->dispatcher->dispatch(new SearchSavedEvent($copy));

        return $copy;
    }

    public function copyLanguage(int $id, Request $request, string $srcLocale, array $destLocales): Testimonial
    {
        $entity = $this->findById($id);
        $entity->setLocale($srcLocale);

        foreach ($destLocales as $destLocale) {
            $entity = $entity->copyToLocale($destLocale);
        }

        // @todo: test with more than one different locale
        $entity->setLocale($this->getLocaleFromRequest($request));

        $this->domainEventCollector->collect(
            new TestimonialCopiedLanguageEvent($entity, $request->request->all())
        );

        $entity = $this->testimonialRepository->save($entity);
        $this->dispatcher->dispatch(new SearchSavedEvent($entity));

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function findByIdAndLocale(int $id, Request $request): Testimonial
    {
        $entity = $this->testimonialRepository->findById($id, (string) $this->getLocaleFromRequest($request));
        if (!$entity) {
            throw new EntityNotFoundException($this->testimonialRepository->getClassName(), $id);
        }

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function findById(int $id): Testimonial
    {
        $entity = $this->testimonialRepository->find($id);
        if (!$entity) {
            throw new EntityNotFoundException($this->testimonialRepository->getClassName(), $id);
        }

        return $entity;
    }

    private function getLocaleFromRequest(Request $request)
    {
        return $request->query->get('locale');
    }

    /**
     * @throws Exception|EntityNotFoundException
     */
    private function mapDataToEntity(Testimonial $entity, array $data): Testimonial
    {
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $entity->setTitle($title);
        }

        $text = $this->getProperty($data, 'text');
        if ($text) {
            $entity->setText($text);
        }

        $published = $this->getProperty($data, 'published');
        if ($published) {
            $entity->setPublished($published);
        }

        $routePath = $this->getProperty($data, 'routePath');
        if ($routePath) {
            $entity->setRoutePath($routePath);
        }

        $contactId = $this->getProperty($data, 'contact');
        if (is_array($contactId) && array_key_exists('id', $contactId)) {
            $contactId = $this->getProperty($contactId, 'id');
        }
        if ($contactId) {
            $contact = $this->contactRepository->findById($contactId);
            if (!$contact) {
                throw new EntityNotFoundException($this->contactRepository->getClassName(), $contactId);
            }
            $entity->setContact($contact);
        } else {
            $entity->setContact(null);
        }

        $showContact = $this->getProperty($data, 'showContact');
        $entity->setShowContact((bool) $showContact);

        $showOrganisation = $this->getProperty($data, 'showOrganisation');
        $entity->setShowOrganisation((bool) $showOrganisation);

        $date = $this->getProperty($data, 'date');
        if ($date) {
            $entity->setDate(new \DateTime($date));
        } else {
            $entity->setDate(null);
        }

        $showDate = $this->getProperty($data, 'showDate');
        $entity->setShowDate((bool) $showDate);

        $rating = $this->getProperty($data, 'rating');
        $entity->setRating($rating ? (string) $rating : null);

        $source = $this->getProperty($data, 'source');
        $entity->setSource($rating ? (string) $source : null);

        $url = $this->getProperty($data, 'url');
        $entity->setUrl($url ?: null);

        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $entity->setImage($image);
        } else {
            $entity->setImage(null);
        }

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     */
    private function mapSettingsToEntity(Testimonial $entity, array $data): Testimonial
    {
        // settings (author, authored) changeable
        $authorId = $this->getProperty($data, 'author');
        if ($authorId) {
            $author = $this->contactRepository->findById($authorId);
            if (!$author) {
                throw new EntityNotFoundException($this->contactRepository->getClassName(), $authorId);
            }
            $entity->setAuthor($author);
        } else {
            $entity->setAuthor(null);
        }

        $authored = $this->getProperty($data, 'authored');
        if ($authored) {
            $entity->setAuthored(new \DateTime($authored));
        } else {
            $entity->setAuthored(null);
        }

        return $entity;
    }

    private function updateRoutesForEntity(Testimonial $entity): void
    {
        $this->routeManager->createOrUpdateByAttributes(
            Testimonial::class,
            (string) $entity->getId(),
            $entity->getLocale(),
            $entity->getRoutePath()
        );
    }

    private function removeRoutesForEntity(Testimonial $entity): void
    {
        $routes = $this->routeRepository->findAllByEntity(
            Testimonial::class,
            (string) $entity->getId(),
            $entity->getLocale()
        );

        foreach ($routes as $route) {
            $this->routeRepository->remove($route);
        }
    }
}
