<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluTestimonialsBundle\Domain\Event\Settings\ModifiedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialsSettings;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/api')]
class SettingsController extends AbstractRestController implements SecuredControllerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainEventCollectorInterface $domainEventCollector,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        path: '/testimonials-settings/{id}.{_format}',
        name: 'sulu_testimonials.get_testimonials-settings',
        requirements: ['_format' => 'json'],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getAction(): Response
    {
        $entity = $this->entityManager->getRepository(TestimonialsSettings::class)->findOneBy([]);

        return $this->handleView($this->view($this->getDataForEntity($entity ?: new TestimonialsSettings())));
    }

    #[Route(
        path: '/testimonials-settings/{id}.{_format}',
        name: 'sulu_testimonials.put_testimonials-settings',
        requirements: ['_format' => 'json'],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putAction(Request $request): Response
    {
        $entity = $this->entityManager->getRepository(TestimonialsSettings::class)->findOneBy([]);
        if (!$entity) {
            $entity = new TestimonialsSettings();
            $this->entityManager->persist($entity);
        }

        $this->domainEventCollector->collect(
            new ModifiedEvent($entity, $request->request->all())
        );

        $data = $request->toArray();
        $this->mapDataToEntity($data, $entity);
        $this->entityManager->flush();

        return $this->handleView($this->view($this->getDataForEntity($entity)));
    }

    protected function getDataForEntity(TestimonialsSettings $entity): array
    {
        return [
            'toggleHeader' => $entity->getToggleHeader(),
            'toggleHero' => $entity->getToggleHero(),
            'toggleBreadcrumbs' => $entity->getToggleBreadcrumbs(),
            'pageTestimonials' => $entity->getPageTestimonials(),
        ];
    }

    protected function mapDataToEntity(array $data, TestimonialsSettings $entity): void
    {
        $entity->setToggleHeader($data['toggleHeader'] ?? null);
        $entity->setToggleHero($data['toggleHero'] ?? null);
        $entity->setToggleBreadcrumbs($data['toggleBreadcrumbs'] ?? null);
        $entity->setPageTestimonials($data['pageTestimonials'] ?? null);
    }

    public function getSecurityContext(): string
    {
        return TestimonialsSettings::SECURITY_CONTEXT;
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->get('locale');
    }
}