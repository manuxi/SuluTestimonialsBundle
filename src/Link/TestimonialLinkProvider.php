<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Link;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfiguration;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialLinkProvider implements LinkProviderInterface
{
    private TestimonialRepository $testimonialRepository;
    private TranslatorInterface $translator;

    public function __construct(TestimonialRepository $testimonialRepository, TranslatorInterface $translator)
    {
        $this->testimonialRepository = $testimonialRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): LinkConfiguration
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('sulu_testimonials.testimonial',[],'admin'))
            ->setResourceKey(Testimonial::RESOURCE_KEY) // the resourceKey of the entity that should be loaded
            ->setListAdapter('table')
            ->setDisplayProperties(['title'])
            ->setOverlayTitle($this->translator->trans('sulu_testimonials.testimonial',[],'admin'))
            ->setEmptyText($this->translator->trans('sulu_testimonials.empty_testimoniallist',[],'admin'))
            ->setIcon('su-tag-pen')
            ->getLinkConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function preload(array $hrefs, $locale, $published = true): array
    {
        if (0 === count($hrefs)) {
            return [];
        }

        $result = [];
        $elements = $this->testimonialRepository->findBy(['id' => $hrefs]); // load items by id
        foreach ($elements as $element) {
            $element->setLocale($locale);
            $result[] = new LinkItem($element->getId(), $element->getTitle(), $element->getRoutePath(), $element->isPublished()); // create link-item foreach item
        }

        return $result;
    }
}
