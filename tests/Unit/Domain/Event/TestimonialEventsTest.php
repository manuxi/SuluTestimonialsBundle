<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Domain\Event;

use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialCopiedLanguageEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialCreatedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialModifiedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialRemovedEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialRestoredEvent;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialUnpublishedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TestimonialEventsTest extends TestCase
{
    use ProphecyTrait;

    public function testCreatedEvent(): void
    {
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getId()->willReturn('123');
        $event = new TestimonialCreatedEvent($testimonial->reveal(), ['title' => 'Test Title']);

        $this->assertEquals(['title' => 'Test Title'], $event->getEventPayload());
        $this->assertSame('created', $event->getEventType());
        $this->assertSame('testimonials', $event->getResourceKey());
        $this->assertSame('123', $event->getResourceId());
        $this->assertSame('Test Title', $event->getResourceTitle());
    }

    public function testModifiedEvent(): void
    {
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getId()->willReturn('123');
        $event = new TestimonialModifiedEvent($testimonial->reveal(), ['title' => 'Modified Title']);

        $this->assertSame('123', $event->getResourceId());
        $this->assertSame('modified', $event->getEventType());
        $this->assertSame('Modified Title', $event->getResourceTitle());
        $this->assertEquals(['title' => 'Modified Title'], $event->getEventPayload());
    }

    public function testRemovedEvent(): void
    {
        $event = new TestimonialRemovedEvent('123', 'Removed Title');
        $this->assertSame('123', $event->getResourceId());
        $this->assertSame('removed', $event->getEventType());
        $this->assertSame('Removed Title', $event->getResourceTitle());
    }

    public function testPublishedEvent(): void
    {
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getId()->willReturn('123');
        $event = new TestimonialPublishedEvent($testimonial->reveal(), ['title' => 'Published Title']);

        $this->assertSame('123', $event->getResourceId());
        $this->assertSame('published', $event->getEventType());
        $this->assertSame('Published Title', $event->getResourceTitle());
    }

    public function testUnpublishedEvent(): void
    {
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getId()->willReturn('123');
        $event = new TestimonialUnpublishedEvent($testimonial->reveal(), ['title' => 'Unpublished Title']);

        $this->assertSame('123', $event->getResourceId());
        $this->assertSame('unpublished', $event->getEventType());
        $this->assertSame('Unpublished Title', $event->getResourceTitle());
    }

    public function testCopiedLanguageEvent(): void
    {
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getId()->willReturn('123');

        $event = new TestimonialCopiedLanguageEvent($testimonial->reveal(), ['title' => 'Copied Title']);

        $this->assertSame('123', $event->getResourceId());
        $this->assertSame('translation_copied', $event->getEventType());
        $this->assertSame('Copied Title', $event->getResourceTitle());
    }

    public function testRestoredEvent(): void
    {
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getId()->willReturn('123');
        $event = new TestimonialRestoredEvent($testimonial->reveal(), ['title' => 'Restored Title']);

        $this->assertSame('123', $event->getResourceId());
        $this->assertSame('restored', $event->getEventType());
        $this->assertSame('Restored Title', $event->getResourceTitle());
    }
}
