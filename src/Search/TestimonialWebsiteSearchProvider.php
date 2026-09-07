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

class TestimonialWebsiteSearchProvider implements ReindexProviderInterface
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public static function getIndex(): string
    {
        return 'website';
    }

    public function total(): ?int
    {
        $locales = $this->getLocales();
        $total = 0;
        foreach ($locales as $locale) {
            $total += $this->testimonialRepository->countPublished($locale);
        }

        return $total;
    }

    public function provide(ReindexConfig $reindexConfig): \Generator
    {
        $locales = $this->getLocales();

        foreach ($locales as $locale) {
            $testimonials = $this->testimonialRepository->findAllByLocale($locale, DimensionContentInterface::STAGE_LIVE);

            foreach ($testimonials as $testimonial) {
                $hasLiveContent = false;
                foreach ($testimonial->getDimensionContents() as $content) {
                    if ($content->getLocale() === $locale && DimensionContentInterface::STAGE_LIVE === $content->getStage()) {
                        $hasLiveContent = true;
                        break;
                    }
                }

                if (!$hasLiveContent) {
                    continue;
                }

                /** @var TestimonialDimensionContent $dimensionContent */
                $dimensionContent = $this->contentAggregator->aggregate(
                    $testimonial,
                    [
                        'locale' => $locale,
                        'stage' => DimensionContentInterface::STAGE_LIVE,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ]
                );

                if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED !== $dimensionContent->getWorkflowPlace()) {
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

        $url = $dimensionContent->getRoute()?->getSlug();

        return [
            'id' => 'testimonial-' . $testimonial->getId() . '-' . $locale,
            'resourceKey' => Testimonial::RESOURCE_KEY,
            'resourceId' => (string) $testimonial->getId(),
            'locale' => $locale,
            'title' => $dimensionContent->getTitle() ?? '',
            'content' => $content,
            'contact' => $contactName,
            'rating' => $dimensionContent->getRating(),
            'url' => $url,
            'mediaId' => $dimensionContent->getImage()?->getId(),
        ];
    }
}