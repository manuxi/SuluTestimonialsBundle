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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


#[Route('/admin/api')]
class SettingsController extends AbstractRestController implements SecuredControllerInterface
{
    private EntityManagerInterface $entityManager;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        EntityManagerInterface $entityManager,
        ViewHandlerInterface $viewHandler,
        DomainEventCollectorInterface $domainEventCollector,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->entityManager = $entityManager;
        $this->domainEventCollector = $domainEventCollector;

        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        path: '/testimonials-settings.{_format}',
        name: 'sulu_testimonials.get_testimonials-settings',
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
        path: '/testimonials-settings.{_format}',
        name: 'sulu_testimonials.put_testimonials-settings',
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
        $entity->setToggleHeader($data['toggleHeader']);
        $entity->setToggleHero($data['toggleHero']);
        $entity->setToggleBreadcrumbs($data['toggleBreadcrumbs']);
        $entity->setPageTestimonials($data['pageTestimonials']);
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