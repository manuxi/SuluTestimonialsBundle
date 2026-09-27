<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Mcp\Tool;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialModifiedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class TestimonialUpdateTool
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentManagerInterface $contentManager,
        private readonly ContentWorkflowInterface $contentWorkflow,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    /**
     * @param array<string, mixed>|null $content
     *
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_testimonial_update',
        title: 'Update Testimonial',
        description: 'Change fields of a testimonial (draft). Only the fields you pass in "content" are changed, for example {"rating": 4, "text": "<p>...</p>"}; "title" is a separate parameter. A published testimonial becomes a draft again and must be published again in the admin.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Testimonial::SECURITY_CONTEXT, PermissionTypes::EDIT),
    ])]
    public function updateTestimonial(string $uuid, string $locale, ?string $title = null, ?array $content = null): array
    {
        try {
            $testimonial = $this->testimonialRepository->findByUuid($uuid);
            if (null === $testimonial) {
                return ['error' => \sprintf('Testimonial "%s" not found.', $uuid)];
            }

            $attributes = ['locale' => $locale, 'stage' => DimensionContentInterface::STAGE_DRAFT];

            // the content manager takes the data as a whole, so start from the current content
            $current = $this->contentManager->normalize($this->contentManager->resolve($testimonial, $attributes));
            $data = ($content ?? []) + (null !== $title ? ['title' => $title] : []) + $current;

            // the rating field is stored as a string
            if (isset($data['rating'])) {
                $data['rating'] = (string) $data['rating'];
            }

            $dimensionContent = $this->contentManager->persist($testimonial, $data, $attributes);

            if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
                $this->contentWorkflow->apply($testimonial, ['locale' => $locale], WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT);
            }

            $this->entityManager->flush();
            $this->domainEventCollector->collect(new TestimonialModifiedEvent($testimonial, $data));

            return ['success' => true, 'uuid' => $uuid, 'locale' => $locale];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to update testimonial: %s', $e->getMessage())];
        }
    }
}
