<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content;

use Manuxi\SuluTestimonialsBundle\Content\TestimonialSmartContentProvider;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class TestimonialSmartContentProviderTest extends TestCase
{
    public function testLiveRowWithoutWorkflowPlaceIsNotShownAsDraft(): void
    {
        // Published in Sulu 3: the live row has no workflowPlace, only workflowPublished.
        $item = $this->mapItem(
            ['workflowPlace' => null, 'workflowPublished' => new \DateTimeImmutable('2026-10-01')],
            DimensionContentInterface::STAGE_LIVE
        );

        $this->assertTrue($item['publishedState']);
        $this->assertNotNull($item['published']);
    }

    public function testLiveRowMigratedFromSuluTwoIsNotShownAsDraft(): void
    {
        $item = $this->mapItem(
            ['workflowPlace' => 'published', 'workflowPublished' => new \DateTimeImmutable('2026-10-01')],
            DimensionContentInterface::STAGE_LIVE
        );

        $this->assertTrue($item['publishedState']);
    }

    public function testDraftRowWithoutPublicationIsShownAsDraft(): void
    {
        $item = $this->mapItem(
            ['workflowPlace' => 'draft', 'workflowPublished' => null],
            DimensionContentInterface::STAGE_DRAFT
        );

        $this->assertFalse($item['publishedState']);
        $this->assertNull($item['published']);
    }

    public function testDraftRowOfPublishedTestimonialIsPublishedState(): void
    {
        $item = $this->mapItem(
            ['workflowPlace' => 'draft', 'workflowPublished' => new \DateTimeImmutable('2026-10-01')],
            DimensionContentInterface::STAGE_DRAFT
        );

        $this->assertTrue($item['publishedState']);
    }

    public function testItemFieldsAreMapped(): void
    {
        $date = new \DateTimeImmutable('2026-09-15');
        $item = $this->mapItem(
            ['id' => 'abc', 'title' => 'Great', 'rating' => 5, 'testimonialDate' => $date],
            DimensionContentInterface::STAGE_LIVE
        );

        $this->assertSame('abc', $item['id']);
        $this->assertSame('Great', $item['title']);
        $this->assertSame('5', $item['rating']);
        $this->assertSame($date, $item['date']);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function mapItem(array $overrides, string $stage): array
    {
        $item = \array_merge(
            ['id' => 'uuid-1', 'title' => 'Title', 'rating' => 5, 'testimonialDate' => null],
            $overrides
        );

        $reflection = new \ReflectionClass(TestimonialSmartContentProvider::class);
        $provider = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('mapQueryResultItem');

        return $method->invoke($provider, $item, $stage);
    }
}
