<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Mcp\Tool;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class TestimonialListTool
{
    private const SUMMARY_FIELDS = [
        'title', 'template', 'url', 'rating', 'source', 'showContact', 'showOrganisation',
        'locale', 'stage', 'published', 'publishedState', 'workflowPlace', 'availableLocales', 'ghostLocale',
    ];

    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly ContentManagerInterface $contentManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_testimonial_list',
        title: 'List Testimonials',
        description: 'List testimonials of one locale (drafts): title, rating, source and workflow state. Use sulu_testimonial_get with a UUID for the full content. Results are paginated with "page" and "limit".',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Testimonial::SECURITY_CONTEXT, PermissionTypes::VIEW),
    ])]
    public function listTestimonials(string $locale, int $page = 1, int $limit = 20): array
    {
        try {
            $testimonials = $this->testimonialRepository->findAllByLocale($locale, DimensionContentInterface::STAGE_DRAFT);
            $total = \count($testimonials);
            $testimonials = \array_slice($testimonials, max(0, ($page - 1) * $limit), $limit);

            $results = [];
            foreach ($testimonials as $testimonial) {
                $dimensionContent = $this->contentManager->resolve($testimonial, [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                ]);
                $normalized = $this->contentManager->normalize($dimensionContent);

                $summary = [];
                foreach (self::SUMMARY_FIELDS as $field) {
                    if (\array_key_exists($field, $normalized)) {
                        $summary[$field] = $normalized[$field];
                    }
                }

                $results[] = ['uuid' => $testimonial->getUuid(), 'data' => $summary];
            }

            return ['testimonials' => $results, 'total' => $total, 'page' => $page, 'limit' => $limit];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to list testimonials: %s', $e->getMessage())];
        }
    }
}
