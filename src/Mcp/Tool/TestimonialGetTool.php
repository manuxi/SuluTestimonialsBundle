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
class TestimonialGetTool
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly ContentManagerInterface $contentManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_testimonial_get',
        title: 'Get Testimonial',
        description: 'Get one testimonial (draft) by UUID with all fields of its template: title, text, rating, source, contact and workflow state.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Testimonial::SECURITY_CONTEXT, PermissionTypes::VIEW),
    ])]
    public function getTestimonial(string $uuid, string $locale): array
    {
        try {
            $testimonial = $this->testimonialRepository->findByUuid($uuid);
            if (null === $testimonial) {
                return ['error' => \sprintf('Testimonial "%s" not found.', $uuid)];
            }

            $dimensionContent = $this->contentManager->resolve($testimonial, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ]);

            return ['uuid' => $testimonial->getUuid()] + $this->contentManager->normalize($dimensionContent);
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to get testimonial: %s', $e->getMessage())];
        }
    }
}
