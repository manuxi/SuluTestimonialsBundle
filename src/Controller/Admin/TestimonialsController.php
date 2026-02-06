<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluTestimonialsBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialCreatedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialModifiedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialRemovedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialUnpublishedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/api')]
class TestimonialsController extends AbstractRestController
{
    public function __construct(
        ViewHandlerInterface $viewHandler,
        TokenStorageInterface $tokenStorage,
        private readonly FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private readonly DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private readonly RestHelperInterface $restHelper,
        private readonly ContentManagerInterface $contentManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly TrashManagerInterface $trashManager,
        private readonly ContentWorkflowInterface $contentWorkflow,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        path: '/testimonials.{_format}',
        name: 'sulu_testimonials.get_testimonials',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function cgetAction(Request $request): Response
    {
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);
        $listKey = Testimonial::LIST_KEY;

        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Testimonial::RESOURCE_KEY,
            [],
            $request->query->all(),
            $listKey
        );

        return $this->handleView($this->view($listRepresentation));
    }

    #[Route(
        path: '/testimonials/{id}.{_format}',
        name: 'sulu_testimonials.get_testimonial',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getAction(Request $request, string $id): Response
    {
        /** @var Testimonial|null $testimonial */
        $testimonial = $this->entityManager->getRepository(Testimonial::class)->findById($id);

        if (!$testimonial) {
            throw new NotFoundHttpException();
        }

        $dimensionAttributes = $this->getDimensionAttributes($request);
        $dimensionContent = $this->contentManager->resolve($testimonial, $dimensionAttributes);

        return $this->handleView($this->view($this->normalize($testimonial, $dimensionContent)));
    }

    #[Route(
        path: '/testimonials.{_format}',
        name: 'sulu_testimonials.post_testimonial',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postAction(Request $request): Response
    {
        $testimonial = new Testimonial();

        $data = $this->getData($request);
        $dimensionAttributes = $this->getDimensionAttributes($request);

        $this->entityManager->persist($testimonial);

        /** @var TestimonialDimensionContent $dimensionContent */
        $dimensionContent = $this->contentManager->persist($testimonial, $data, $dimensionAttributes);

        $this->entityManager->flush();

        $this->domainEventCollector->collect(new TestimonialCreatedEvent($testimonial, $data));

        if ('publish' === $request->query->get('action')) {
            $this->contentWorkflow->apply(
                $testimonial,
                ['locale' => $dimensionAttributes['locale']],
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
            );
            $dimensionContent = $this->contentManager->resolve($testimonial, $dimensionAttributes);
            $this->entityManager->flush();
            $this->domainEventCollector->collect(new TestimonialPublishedEvent($testimonial, $data));
        }

        return $this->handleView($this->view($this->normalize($testimonial, $dimensionContent), 201));
    }

    #[Route(
        path: '/testimonials/{id}.{_format}',
        name: 'sulu_testimonials.post_testimonial_trigger',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postTriggerAction(string $id, Request $request): Response
    {
        /** @var Testimonial|null $testimonial */
        $testimonial = $this->entityManager->getRepository(Testimonial::class)->findById($id);

        if (!$testimonial) {
            throw new NotFoundHttpException();
        }

        $dimensionAttributes = $this->getDimensionAttributes($request);
        $action = $request->query->get('action');

        switch ($action) {
            case 'copy_locale':
                $dimensionContent = $this->contentManager->copy(
                    $testimonial,
                    [
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $request->query->get('src'),
                    ],
                    $testimonial,
                    [
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $request->query->get('dest'),
                    ]
                );

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($testimonial, $dimensionContent)));

            case 'unpublish':
                $this->contentWorkflow->apply(
                    $testimonial,
                    ['locale' => $dimensionAttributes['locale']],
                    WorkflowInterface::WORKFLOW_TRANSITION_UNPUBLISH
                );
                $dimensionContent = $this->contentManager->resolve($testimonial, $dimensionAttributes);

                $this->entityManager->flush();
                $this->domainEventCollector->collect(new TestimonialUnpublishedEvent($testimonial, $request->query->all()));

                return $this->handleView($this->view($this->normalize($testimonial, $dimensionContent)));

            case 'remove_draft':
                $this->contentWorkflow->apply(
                    $testimonial,
                    ['locale' => $dimensionAttributes['locale']],
                    WorkflowInterface::WORKFLOW_TRANSITION_REMOVE_DRAFT
                );
                $dimensionContent = $this->contentManager->resolve($testimonial, $dimensionAttributes);

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($testimonial, $dimensionContent)));

            case 'restore':
                $version = (int) $request->query->get('version');
                $dimensionContent = $this->contentManager->copy(
                    $testimonial,
                    [
                        'stage' => $dimensionAttributes['stage'] ?? DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $dimensionAttributes['locale'] ?? null,
                        'version' => $version,
                    ],
                    $testimonial,
                    [
                        'stage' => $dimensionAttributes['stage'] ?? DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $dimensionAttributes['locale'] ?? null,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ],
                    [
                        'ignoredAttributes' => ['url'],
                    ]
                );

                $this->entityManager->flush();
                $this->domainEventCollector->collect(new TestimonialModifiedEvent($testimonial, $request->query->all()));

                return $this->handleView($this->view($this->normalize($testimonial, $dimensionContent)));

            default:
                throw new RestException('Unrecognized action: ' . $action);
        }
    }

    #[Route(
        path: '/testimonials/{id}.{_format}',
        name: 'sulu_testimonials.put_testimonial',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putAction(Request $request, string $id): Response
    {
        /** @var Testimonial|null $testimonial */
        $testimonial = $this->entityManager->getRepository(Testimonial::class)->findById($id);

        if (!$testimonial) {
            throw new NotFoundHttpException();
        }

        $data = $this->getData($request);
        $dimensionAttributes = $this->getDimensionAttributes($request);

        /** @var TestimonialDimensionContent $dimensionContent */
        $dimensionContent = $this->contentManager->persist($testimonial, $data, $dimensionAttributes);

        if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
            $this->contentWorkflow->apply(
                $testimonial,
                ['locale' => $dimensionAttributes['locale']],
                WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT
            );
            $dimensionContent = $this->contentManager->resolve($testimonial, $dimensionAttributes);
        }

        $this->entityManager->flush();
        $this->domainEventCollector->collect(new TestimonialModifiedEvent($testimonial, $data));

        if ('publish' === $request->query->get('action')) {
            $this->contentWorkflow->apply(
                $testimonial,
                ['locale' => $dimensionAttributes['locale']],
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
            );
            $dimensionContent = $this->contentManager->resolve($testimonial, $dimensionAttributes);
            $this->entityManager->flush();
            $this->domainEventCollector->collect(new TestimonialPublishedEvent($testimonial, $data));
        }

        return $this->handleView($this->view($this->normalize($testimonial, $dimensionContent)));
    }

    #[Route(
        path: '/testimonials/{id}.{_format}',
        name: 'sulu_testimonials.delete_testimonial',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['DELETE']
    )]
    public function deleteAction(Request $request, string $id): Response
    {
        /** @var Testimonial $testimonial */
        $testimonial = $this->entityManager->getRepository(Testimonial::class)->findById($id);

        if (!$testimonial) {
            throw new NotFoundHttpException();
        }

        $testimonialId = $testimonial->getId();
        $testimonialTitle = '';

        $locale = $request->query->get('locale');
        if ($locale) {
            foreach ($testimonial->getDimensionContents() as $dc) {
                if ($dc->getLocale() === $locale) {
                    $testimonialTitle = $dc->getTitle() ?? '';
                    break;
                }
            }
        }

        $this->trashManager->store(Testimonial::RESOURCE_KEY, $testimonial);

        $this->entityManager->remove($testimonial);
        $this->domainEventCollector->collect(new TestimonialRemovedEvent($testimonialId, $testimonialTitle));
        $this->entityManager->flush();

        return new Response('', 204);
    }

    #[Route(
        path: '/testimonials/{id}/versions.{_format}',
        name: 'sulu_testimonials.get_testimonial_versions',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getVersionsAction(Request $request, string $id): Response
    {
        $locale = $request->query->get('locale');

        /** @var DoctrineFieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors('testimonials_versions');

        /** @var DoctrineListBuilder $listBuilder */
        $listBuilder = $this->listBuilderFactory->create(Testimonial::class);
        $listBuilder->setParameter('locale', $locale);
        $listBuilder->setParameter('id', $id);
        $listBuilder->setIdField($fieldDescriptors['id']);
        $listBuilder->sort($fieldDescriptors['version'], 'DESC');
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $result = $listBuilder->execute();
        $listRepresentation = new PaginatedRepresentation(
            $result,
            'testimonials_versions',
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            $listBuilder->count(),
        );

        return $this->handleView($this->view($listRepresentation));
    }

    protected function getDimensionAttributes(Request $request): array
    {
        $attributes = $request->query->all();
        if (!isset($attributes['stage'])) {
            $attributes['stage'] = DimensionContentInterface::STAGE_DRAFT;
        }

        return $attributes;
    }

    protected function getData(Request $request): array
    {
        if ('application/json' === $request->headers->get('Content-Type')) {
            return $request->toArray();
        }

        return $request->request->all();
    }

    protected function normalize(Testimonial $testimonial, DimensionContentInterface $dimensionContent): array
    {
        return $this->contentManager->normalize($dimensionContent);
    }
}
