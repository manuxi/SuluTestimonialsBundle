<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Search;

use CmsIg\Seal\Reindex\ReindexConfig;
use CmsIg\Seal\Reindex\ReindexProviderInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

class TestimonialAdminSearchProvider implements ReindexProviderInterface
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public static function getIndex(): string
    {
        return 'admin';
    }

    public function total(): ?int
    {
        return $this->testimonialRepository->countAll();
    }

    public function provide(ReindexConfig $reindexConfig): \Generator
    {
        $locales = $this->getLocales();

        foreach ($locales as $locale) {
            $testimonials = $this->testimonialRepository->findAll();

            foreach ($testimonials as $testimonial) {
                /** @var TestimonialDimensionContent $dimensionContent */
                $dimensionContent = $this->contentAggregator->aggregate(
                    $testimonial,
                    [
                        'locale' => $locale,
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ]
                );

                if (!$dimensionContent->getTitle()) {
                    continue;
                }

                yield $this->createDocument($testimonial, $dimensionContent, $locale);
            }
        }
    }

    private function getLocales(): array
    {
        $locales = [];
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            foreach ($webspace->getAllLocalizations() as $localization) {
                $locales[$localization->getLocale()] = true;
            }
        }

        return array_keys($locales);
    }

    private function createDocument(Testimonial $testimonial, TestimonialDimensionContent $dimensionContent, string $locale): array
    {
        $content = array_filter([
            $dimensionContent->getText(),
            $dimensionContent->getSource(),
        ]);

        $contact = $dimensionContent->getContact();
        $contactName = $contact ? trim($contact->getFirstName() . ' ' . $contact->getLastName()) : null;

        return [
            'id' => 'testimonial-' . $testimonial->getId() . '-' . $locale . '-draft',
            'resourceKey' => Testimonial::RESOURCE_KEY,
            'resourceId' => (string) $testimonial->getId(),
            'locale' => $locale,
            'securityContext' => Testimonial::SECURITY_CONTEXT,
            'title' => $dimensionContent->getTitle() ?? '',
            'content' => $content,
            'contact' => $contactName,
            'rating' => $dimensionContent->getRating(),
            'mediaId' => $dimensionContent->getImage()?->getId(),
            'changedAt' => $dimensionContent->getChanged()?->format('c'),
            'createdAt' => $dimensionContent->getCreated()?->format('c'),
            'published' => WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace(),
        ];
    }
}