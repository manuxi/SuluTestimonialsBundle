<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Controller\Website;

use JMS\Serializer\SerializerBuilder;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialsController extends AbstractController
{
    private TranslatorInterface $translator;
    private TestimonialRepository $repository;
    private WebspaceManagerInterface $webspaceManager;
    private TemplateAttributeResolverInterface $templateAttributeResolver;
    private RouteRepositoryInterface $routeRepository;

    public function __construct(
        RequestStack $requestStack,
        MediaManagerInterface $mediaManager,
        TestimonialRepository $repository,
        WebspaceManagerInterface $webspaceManager,
        TranslatorInterface $translator,
        TemplateAttributeResolverInterface $templateAttributeResolver,
        RouteRepositoryInterface $routeRepository,
    ) {
        parent::__construct($requestStack, $mediaManager);

        $this->repository = $repository;
        $this->webspaceManager = $webspaceManager;
        $this->translator = $translator;
        $this->templateAttributeResolver = $templateAttributeResolver;
        $this->routeRepository = $routeRepository;
    }

    /**
     * @throws \Exception
     */
    public function indexAction(Testimonial $testimonial, string $view = '@SuluTestimonials/testimonial', bool $preview = false, bool $partial = false): Response
    {
        $viewTemplate = $this->getViewTemplate($view, $this->request, $preview);

        $parameters = $this->templateAttributeResolver->resolve([
            'testimonial' => $testimonial,
            'content' => [
                'title' => $this->translator->trans('sulu_testimonials.testimonials'),
                'title' => $testimonial->getTitle(),
            ],
            /*            'path'          => $testimonial->getRoutePath(), */
            'extension' => $this->extractExtension($testimonial),
            'localizations' => $this->getLocalizationsArrayForEntity($testimonial),
            'created' => $testimonial->getCreated(),
        ]);

        return $this->prepareResponse($viewTemplate, $parameters, $preview, $partial);
    }

    /**
     * With the help of this method the corresponding localisations for the
     * current testimonials are found e.g. to be linked in the language switcher.
     *
     * @return array<string, array>
     */
    protected function getLocalizationsArrayForEntity(Testimonial $testimonial): array
    {
        $routes = $this->routeRepository->findAllByEntity(Testimonial::class, (string) $testimonial->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getPath(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = ['locale' => $route->getLocale(), 'url' => $url];
        }

        return $localizations;
    }

    private function extractExtension(Testimonial $testimonial): array
    {
        $serializer = SerializerBuilder::create()->build();

        return $serializer->toArray($testimonial->getExt());
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                WebspaceManagerInterface::class,
                RouteRepositoryInterface::class,
                TemplateAttributeResolverInterface::class,
            ]
        );
    }
}
