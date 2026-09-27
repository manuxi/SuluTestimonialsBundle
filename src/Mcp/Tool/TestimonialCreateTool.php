<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Mcp\Tool;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialCreatedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class TestimonialCreateTool
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentManagerInterface $contentManager,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    /**
     * @param array<string, mixed>|null $content
     *
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_testimonial_create',
        title: 'Create Testimonial',
        description: 'Create a testimonial as a draft (publish it in the admin). Pass the template ("testimonial" or "testimonial_details"), the title and the template fields in "content", for example {"url": {"page": {"uuid": "<parent page uuid>", "path": "/referenzen"}, "suffix": "/max-mustermann"}, "contact": 3, "rating": 5, "source": "Google", "text": "<p>...</p>"}. The template "testimonial" needs a page tree route for "url" and a contact id (see sulu_contact_list); "testimonial_details" takes "url" as a path string. "rating" is a whole number on the star scale (1 to 5).',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Testimonial::SECURITY_CONTEXT, PermissionTypes::ADD),
    ])]
    public function createTestimonial(string $locale, string $title, string $template = 'testimonial', ?array $content = null): array
    {
        try {
            $data = ['template' => $template, 'title' => $title] + ($content ?? []);

            // the rating field is stored as a string
            if (isset($data['rating'])) {
                $data['rating'] = (string) $data['rating'];
            }

            $testimonial = new Testimonial();
            $this->entityManager->persist($testimonial);
            $this->contentManager->persist($testimonial, $data, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ]);

            $this->entityManager->flush();
            $this->domainEventCollector->collect(new TestimonialCreatedEvent($testimonial, $data));

            return ['success' => true, 'uuid' => $testimonial->getUuid(), 'title' => $title, 'locale' => $locale, 'workflowPlace' => 'draft'];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to create testimonial: %s', $e->getMessage())];
        }
    }
}
