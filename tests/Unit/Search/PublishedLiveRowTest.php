<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Search;

use CmsIg\Seal\EngineInterface;
use CmsIg\Seal\Reindex\ReindexConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Manuxi\SuluTestimonialsBundle\Search\TestimonialSearchListener;
use Manuxi\SuluTestimonialsBundle\Search\TestimonialWebsiteSearchProvider;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceCollection;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * Sulu 3 leaves workflowPlace NULL on the live dimension when publishing; only rows migrated from Sulu 2
 * carry "published". Such live rows must still be found by the search.
 */
class PublishedLiveRowTest extends TestCase
{
    public function testReindexIncludesLiveRowWithoutWorkflowPlace(): void
    {
        $documents = $this->reindex(null);

        $this->assertCount(1, $documents);
        $this->assertSame('testimonial-abc-de', $documents[0]['id']);
    }

    public function testReindexIncludesLiveRowMigratedFromSuluTwo(): void
    {
        $this->assertCount(1, $this->reindex('published'));
    }

    public function testPublishingMarksTheAdminDocumentAsPublishedWithoutWorkflowPlace(): void
    {
        $aggregator = $this->createMock(ContentAggregatorInterface::class);
        $aggregator->method('aggregate')->willReturn($this->createLiveContent(null));

        $saved = [];
        $engine = $this->createMock(EngineInterface::class);
        $engine->method('saveDocument')->willReturnCallback(function (string $index, array $document) use (&$saved) {
            $saved[$index] = $document;

            return null;
        });

        $listener = new TestimonialSearchListener($engine, $this->createWebspaceManager(), $aggregator);
        $listener->onPublished(new TestimonialPublishedEvent($this->createTestimonial(), []));

        $this->assertTrue($saved['admin']['published']);
        $this->assertArrayHasKey('website', $saved);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function reindex(?string $workflowPlace): array
    {
        $repository = $this->createMock(TestimonialRepository::class);
        $repository->method('findAllByLocale')->willReturn([$this->createTestimonial()]);

        $aggregator = $this->createMock(ContentAggregatorInterface::class);
        $aggregator->method('aggregate')->willReturn($this->createLiveContent($workflowPlace));

        $provider = new TestimonialWebsiteSearchProvider($repository, $this->createWebspaceManager(), $aggregator);

        return \iterator_to_array($provider->provide(new ReindexConfig()), false);
    }

    private function createTestimonial(): Testimonial
    {
        $testimonial = $this->createMock(Testimonial::class);
        $testimonial->method('getId')->willReturn('abc');
        $testimonial->method('getDimensionContents')->willReturn(new ArrayCollection([$this->createLiveContent(null)]));

        return $testimonial;
    }

    private function createLiveContent(?string $workflowPlace): TestimonialDimensionContent
    {
        $content = $this->createMock(TestimonialDimensionContent::class);
        $content->method('getLocale')->willReturn('de');
        $content->method('getStage')->willReturn(DimensionContentInterface::STAGE_LIVE);
        $content->method('getWorkflowPlace')->willReturn($workflowPlace);
        $content->method('getTitle')->willReturn('von Erika');

        return $content;
    }

    private function createWebspaceManager(): WebspaceManagerInterface
    {
        $localization = $this->createMock(Localization::class);
        $localization->method('getLocale')->willReturn('de');

        $webspace = $this->createMock(Webspace::class);
        $webspace->method('getKey')->willReturn('site');
        $webspace->method('getAllLocalizations')->willReturn([$localization]);

        $manager = $this->createMock(WebspaceManagerInterface::class);
        $manager->method('getWebspaceCollection')->willReturn(new WebspaceCollection(['site' => $webspace]));

        return $manager;
    }
}
