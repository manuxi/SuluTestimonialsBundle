<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Preview;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\PreviewBundle\Preview\PreviewContext;
use Sulu\Bundle\PreviewBundle\Preview\Provider\PreviewDefaultsProviderInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class TestimonialsObjectProvider implements PreviewDefaultsProviderInterface
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public function getDefaults(PreviewContext $previewContext): array
    {
        $testimonial = $this->testimonialRepository->findById((int) $previewContext->getId());

        if (!$testimonial) {
            return [];
        }

        // Resolve the DimensionContent for the requested locale
        $dimensionContent = $this->contentAggregator->aggregate(
            $testimonial,
            [
                'locale' => $previewContext->getLocale(),
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ]
        );

        if (!$dimensionContent) {
            return [];
        }

        return [
            '_controller' => 'Manuxi\SuluTestimonialsBundle\Controller\Website\TestimonialsController::indexAction',
            'testimonial' => $testimonial,
            'dimensionContent' => $dimensionContent,
        ];
    }

    public function updateValues(PreviewContext $previewContext, array $defaults, array $data): array
    {
        // Update dimension content with preview data
        $dimensionContent = $defaults['dimensionContent'] ?? null;

        if ($dimensionContent) {
            if (isset($data['title'])) {
                $dimensionContent->setTitle($data['title']);
            }
            if (isset($data['text'])) {
                $dimensionContent->setText($data['text']);
            }
            if (isset($data['rating'])) {
                $dimensionContent->setRating($data['rating']);
            }
            if (isset($data['source'])) {
                $dimensionContent->setSource($data['source']);
            }
            // Add other fields from TestimonialDimensionContent here as needed
        }

        return $defaults;
    }

    public function updateContext(PreviewContext $previewContext, array $defaults, array $context): array
    {
        $dimensionContent = $defaults['dimensionContent'] ?? null;

        if ($dimensionContent && \array_key_exists('template', $context)) {
            // Testimonials might not use templates in the same way as Pages/Events, but if they do:
            // $dimensionContent->setTemplateKey($context['template']);
        }

        return $defaults;
    }

    public function getSecurityContext(PreviewContext $previewContext): ?string
    {
        return Testimonial::SECURITY_CONTEXT;
    }
}
