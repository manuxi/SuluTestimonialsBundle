<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Entity;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class TestimonialTest extends TestCase
{
    use ProphecyTrait;

    private $testimonial;

    protected function setUp(): void
    {
        $this->testimonial = new Testimonial();
    }

    public function testGetIdDefault(): void
    {
        $this->assertNull($this->testimonial->getId());
    }

    public function testAddRemoveDimensionContent(): void
    {
        $content1 = $this->prophesize(TestimonialDimensionContent::class);
        $content1->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $content1->getLocale()->willReturn('en');

        $this->testimonial->addDimensionContent($content1->reveal());
        $this->assertCount(1, $this->testimonial->getDimensionContents());

        $content2 = $this->prophesize(TestimonialDimensionContent::class);
        $content2->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);
        $content2->getLocale()->willReturn('en');

        $this->testimonial->addDimensionContent($content2->reveal());
        $this->assertCount(2, $this->testimonial->getDimensionContents());

        $this->testimonial->removeDimensionContent($content1->reveal());
        $this->assertCount(1, $this->testimonial->getDimensionContents());
    }

    public function testResourceKey(): void
    {
        $this->assertSame(Testimonial::RESOURCE_KEY, $this->testimonial::RESOURCE_KEY);
        $this->assertSame('testimonials', $this->testimonial::RESOURCE_KEY);
    }

    public function testSecurityContext(): void
    {
        $this->assertSame(Testimonial::SECURITY_CONTEXT, $this->testimonial::SECURITY_CONTEXT);
    }
}
