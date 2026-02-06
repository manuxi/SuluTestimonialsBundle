<?php
declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Teaser;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\AdminBundle\Teaser\Configuration\TeaserConfiguration;
use Sulu\Bundle\AdminBundle\Teaser\Provider\TeaserProviderInterface;
use Sulu\Bundle\AdminBundle\Teaser\Teaser;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentEnhancer\ContentEnhancerInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialTeaserProvider implements TeaserProviderInterface
{
    public function __construct(
        protected TestimonialRepository      $testimonialRepository,
        protected ContentAggregatorInterface $contentAggregator,
        protected ContentEnhancerInterface   $contentEnhancer,
        protected TranslatorInterface        $translator,
    )
    {
    }

    public function getConfiguration(): TeaserConfiguration
    {
        return new TeaserConfiguration(
            $this->translator->trans('sulu_testimonials.testimonial', [], 'admin'),
            Testimonial::RESOURCE_KEY,
            'table',
            ['title'],
            $this->translator->trans('sulu_testimonials.select_testimonial', [], 'admin'),
        );
    }

    /**
     * @param array<string> $ids
     *
     * @return Teaser[]
     */
    public function find(array $ids, $locale): array
    {
        if (0 === count($ids)) {
            return [];
        }
        $testimonials = $this->findTestimonialsByUuids($ids, $locale);
        $teasers = [];
        foreach ($testimonials as $testimonial) {
            $teaser = $this->createTeaserFromTestimonial($testimonial, $locale);
            if (null !== $teaser) {
                $teasers[] = $teaser;
            }
        }
        return $teasers;
    }

    /**
     * @param array<string> $uuids
     *
     * @return array<Testimonial>
     */
    private function findTestimonialsByUuids(array $uuids, string $locale): array
    {
        /** @var array<Testimonial> $testimonials */
        $testimonials = $this->testimonialRepository->findByUuids(
            $uuids,
            $locale,
            DimensionContentInterface::STAGE_LIVE
        );
        $uuidPositions = array_flip($uuids);
        usort(
            $testimonials,
            static fn(Testimonial $a, Testimonial $b) => ($uuidPositions[$a->getUuid()] ?? 0) - ($uuidPositions[$b->getUuid()] ?? 0)
        );
        return $testimonials;
    }

    private function createTeaserFromTestimonial(Testimonial $testimonial, string $locale): ?Teaser
    {
        try {
            /** @var TestimonialDimensionContent|null $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $testimonial,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );
            if (null === $dimensionContent) {
                return null;
            }
            $enhancedContent = $this->contentEnhancer->enhance($dimensionContent);
            if ($enhancedContent instanceof TestimonialDimensionContent) {
                $dimensionContent = $enhancedContent;
            }
        } catch (ContentNotFoundException) {
            return null;
        }
        $title = $this->resolveTitle($dimensionContent);
        if (null === $title) {
            return null;
        }
        return new Teaser(
            $testimonial->getUuid(),
            Testimonial::RESOURCE_KEY,
            $locale,
            $title,
            $this->resolveDescription($dimensionContent),
            $this->resolveMoreText($dimensionContent),
            $this->resolveUrl($dimensionContent),
            $this->resolveMediaId($dimensionContent),
            $this->getAttributes($dimensionContent)
        );
    }

    protected function resolveTitle(TestimonialDimensionContent $dimensionContent): ?string
    {
        $title = $dimensionContent->getExcerptTitle() ?? $dimensionContent->getTitle();
        return is_string($title) && '' !== $title ? $title : null;
    }

    protected function resolveDescription(TestimonialDimensionContent $dimensionContent): ?string
    {
        $text = $dimensionContent->getText();
        if (!empty($text)) {
            return mb_substr(strip_tags($text), 0, 200);
        }
        $excerptDescription = $dimensionContent->getExcerptDescription();
        if (!empty($excerptDescription)) {
            return strip_tags($excerptDescription);
        }
        return null;
    }

    protected function resolveMoreText(TestimonialDimensionContent $dimensionContent): ?string
    {
        $moreText = $dimensionContent->getExcerptMore();
        return '' !== ($moreText ?? '') ? $moreText : null;
    }

    protected function resolveUrl(TestimonialDimensionContent $dimensionContent): ?string
    {
        $route = $dimensionContent->getRoute();
        $url = $route?->getSlug() ?? null;
        return is_string($url) ? $url : null;
    }

    protected function resolveMediaId(TestimonialDimensionContent $dimensionContent): ?int
    {
        $image = $dimensionContent->getImage();
        if (null !== $image) {
            return $image->getId();
        }
        $excerptImage = $dimensionContent->getExcerptImage();
        return $excerptImage['id'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAttributes(TestimonialDimensionContent $dimensionContent): array
    {
        return [];
    }
}