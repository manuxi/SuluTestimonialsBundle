<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Link;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfiguration;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialLinkProvider implements LinkProviderInterface
{
    public function __construct(
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly TestimonialRepository $testimonialRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationBuilder(): LinkConfigurationBuilder
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('sulu_testimonials.testimonial', [], 'admin'))
            ->setResourceKey(Testimonial::RESOURCE_KEY)
            ->setListAdapter('table')
            ->setDisplayProperties(['title'])
            ->setOverlayTitle($this->translator->trans('sulu_testimonials.testimonial', [], 'admin'))
            ->setEmptyText($this->translator->trans('sulu_testimonials.empty_testimoniallist', [], 'admin'))
            ->setIcon('su-tag-pen');
    }

    /**
     * {@inheritdoc}
     */
    public function preload(array $hrefs, $locale, $published = true): iterable
    {
        if (0 === count($hrefs)) {
            return [];
        }

        $dimensionAttributes = [
            'locale' => $locale,
            'stage' => $published ? DimensionContentInterface::STAGE_LIVE : DimensionContentInterface::STAGE_DRAFT,
        ];

        // We can't easily rely on findByIds if not implemented in repo, but we can find by criteria
        // Assuming findBy works for basic entity loading
        $elements = $this->testimonialRepository->findBy(['id' => $hrefs]);

        foreach ($elements as $element) {
            try {
                /** @var TestimonialDimensionContent $dimensionContent */
                $dimensionContent = $this->contentAggregator->aggregate($element, $dimensionAttributes);

                $title = $dimensionContent->getTitle() ?? '';
                // Testimonials might not be routable in the same way, but let's assume they are if we link to them
                // Or maybe they don't have a route? If not, what's the link?
                // TestimonialLinkProvider assumes they are linkable.
                // TestimonialDimensionContent uses RoutableTrait?
                // If not routable, maybe we shouldn't supply a URL?
                // But LinkItem expects a URL.
                // Let's assume RoutableTrait is present and used.
                $url = $dimensionContent->getRoute()?->getSlug() ?? '';
                $isPublished = $dimensionContent->getWorkflowPlace() === WorkflowInterface::WORKFLOW_PLACE_PUBLISHED;

                yield new LinkItem((string) $element->getId(), $title, $url, $isPublished);
            } catch (\Exception $e) {
                // Skip if aggregation fails
                continue;
            }
        }
    }
}
