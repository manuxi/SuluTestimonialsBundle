<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Controller\Admin;

use Manuxi\SuluTestimonialsBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\Models\TestimonialExcerptModel;
use Manuxi\SuluTestimonialsBundle\Entity\Models\TestimonialModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Models\TestimonialSeoModel;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\MissingParameterException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("testimonial")
 */
class TestimonialsController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    private TestimonialModel $testimonialModel;
    private TestimonialSeoModel $testimonialSeoModel;
    private TestimonialExcerptModel $testimonialExcerptModel;
    private DoctrineListRepresentationFactory $doctrineListRepresentationFactory;
    private SecurityCheckerInterface $securityChecker;
    private TrashManagerInterface $trashManager;

    public function __construct(
        TestimonialModel $testimonialModel,
        TestimonialSeoModel $testimonialSeoModel,
        TestimonialExcerptModel $testimonialExcerptModel,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        SecurityCheckerInterface $securityChecker,
        ViewHandlerInterface $viewHandler,
        TrashManagerInterface $trashManager,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        parent::__construct($viewHandler, $tokenStorage);
        $this->testimonialModel                 = $testimonialModel;
        $this->testimonialSeoModel              = $testimonialSeoModel;
        $this->testimonialExcerptModel          = $testimonialExcerptModel;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->securityChecker                   = $securityChecker;
        $this->trashManager = $trashManager;
    }

    public function cgetAction(Request $request): Response
    {
        $locale             = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Testimonial::RESOURCE_KEY,
            [],
            ['locale' => $locale]
        );

        return $this->handleView($this->view($listRepresentation));

    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws EntityNotFoundException
     */
    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->testimonialModel->get($id, $request);
        return $this->handleView($this->view($entity));
    }

    /**
     * @param Request $request
     * @return Response
     * @throws EntityNotFoundException
     */
    public function postAction(Request $request): Response
    {
        $entity = $this->testimonialModel->create($request);
        return $this->handleView($this->view($entity, 201));
    }

    /**
     * @Rest\Post("/testimonials/{id}")
     *
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws MissingParameterException
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);

        try {
            switch ($action) {
                case 'publish':
                    $entity = $this->testimonialModel->publish($id, $request);
                    break;
                case 'unpublish':
                    $entity = $this->testimonialModel->unpublish($id, $request);
                    break;
                case 'copy':
                    $entity = $this->testimonialModel->copy($id, $request);
                    break;
                case 'copy-locale':
                    $locale = $this->getRequestParameter($request, 'locale', true);
                    $srcLocale = $this->getRequestParameter($request, 'src', false, $locale);
                    $destLocales = $this->getRequestParameter($request, 'dest', true);
                    $destLocales = explode(',', $destLocales);

                    foreach ($destLocales as $destLocale) {
                        $this->securityChecker->checkPermission(
                            new SecurityCondition($this->getSecurityContext(), $destLocale),
                            PermissionTypes::EDIT
                        );
                    }

                    $entity = $this->testimonialModel->copyLanguage($id, $request, $srcLocale, $destLocales);
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
            return $this->handleView($view);
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws EntityNotFoundException
     */
    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->testimonialModel->update($id, $request);

        $this->testimonialSeoModel->updateTestimonialSeo($entity->getSeo(), $request);
        $this->testimonialExcerptModel->updateTestimonialExcerpt($entity->getExcerpt(), $request);

        return $this->handleView($this->view($entity));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws EntityNotFoundException
     */
    public function deleteAction(int $id, Request $request): Response
    {
        $entity = $this->testimonialModel->get($id, $request);

        $this->trashManager->store(Testimonial::RESOURCE_KEY, $entity);

        $this->testimonialModel->delete($entity);
        return $this->handleView($this->view(null, 204));
    }

    public function getSecurityContext(): string
    {
        return Testimonial::SECURITY_CONTEXT;
    }

}
