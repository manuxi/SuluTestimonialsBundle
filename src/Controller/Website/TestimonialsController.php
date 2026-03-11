<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Controller\Website;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\PreviewBundle\Preview\Preview;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Twig\Environment;

class TestimonialsController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly TemplateAttributeResolverInterface $templateAttributeResolver,
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly RequestStack $requestStack,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly EntityManagerInterface $entityManager,
        private readonly int $ratingMaxValue = 5,
    ) {
    }

    /**
     * Called via route defaults provider (object = DimensionContent, view = template path)
     * or directly for preview (testimonial = Testimonial entity).
     */
    public function indexAction(
        ?TestimonialDimensionContent $object = null,
        ?Testimonial $testimonial = null,
        ?string $view = null,
        bool $preview = false,
        bool $partial = false,
    ): Response {
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request ? $request->getLocale() : 'en';

        // Route defaults provider passes resolved DimensionContent as 'object'
        $content = $object;

        // Fallback for preview or legacy: resolve from Testimonial entity
        if (!$content && $testimonial) {
            $stage = $preview ? DimensionContentInterface::STAGE_DRAFT : DimensionContentInterface::STAGE_LIVE;

            /** @var TestimonialDimensionContent|null $content */
            $content = $this->contentAggregator->aggregate(
                $testimonial,
                [
                    'locale' => $locale,
                    'stage' => $stage,
                ]
            );

            if (!$content || !$content->getTitle()) {
                $content = $this->findDimensionContentInCollection($testimonial, $locale, $stage);
            }
        }

        if (!$content) {
            throw new NotAcceptableHttpException(\sprintf('No content found for locale "%s".', $locale));
        }

        if (!$content->getContact() && $content->getContactId()) {
            $contact = $this->entityManager->find(ContactInterface::class, $content->getContactId());
            if ($contact) {
                $content->setContact($contact);
            }
        }

        $parameters = $this->templateAttributeResolver->resolve([
            'testimonial' => $content,
            'localizations' => $this->getLocalizationsArrayForEntity($content->getResource()),
            'ratingMaxValue' => $this->ratingMaxValue,
        ]);

        $viewTemplate = ($view ?? '@SuluTestimonials/testimonial') . '.html.twig';

        if (!$this->twig->getLoader()->exists($viewTemplate)) {
            throw new NotAcceptableHttpException(\sprintf('Template "%s" does not exist.', $viewTemplate));
        }

        if ($partial) {
            $twigTemplate = $this->twig->load($viewTemplate);
            $rendered = $twigTemplate->renderBlock('content', $this->twig->mergeGlobals($parameters));
        } elseif ($preview) {
            $parameters['previewParentTemplate'] = $viewTemplate;
            $parameters['previewContentReplacer'] = Preview::CONTENT_REPLACER;
            $rendered = $this->twig->render('@SuluWebsite/Preview/preview.html.twig', $parameters);
        } else {
            $rendered = $this->twig->render($viewTemplate, $parameters);
        }

        return new Response($rendered);
    }

    /**
     * Fallback method to find DimensionContent in the Testimonial's collection.
     * Used when ContentAggregator doesn't return content (e.g., during preview).
     */
    private function findDimensionContentInCollection(Testimonial $testimonial, string $locale, string $stage): ?TestimonialDimensionContent
    {
        foreach ($testimonial->getDimensionContents() as $dimensionContent) {
            if ($dimensionContent->getLocale() === $locale && $dimensionContent->getStage() === $stage) {
                return $dimensionContent;
            }
        }

        // Try draft stage if live not found
        if ($stage === DimensionContentInterface::STAGE_LIVE) {
            foreach ($testimonial->getDimensionContents() as $dimensionContent) {
                if ($dimensionContent->getLocale() === $locale && $dimensionContent->getStage() === DimensionContentInterface::STAGE_DRAFT) {
                    return $dimensionContent;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, array{locale: string, url: string|null}>
     */
    protected function getLocalizationsArrayForEntity(Testimonial $testimonial): array
    {
        $routes = $this->routeRepository->findBy([
            'resourceKey' => Testimonial::RESOURCE_KEY,
            'resourceId' => (string) $testimonial->getId(),
        ]);

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getSlug(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = ['locale' => $route->getLocale(), 'url' => $url];
        }

        return $localizations;
    }
}
