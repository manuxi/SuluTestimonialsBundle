<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Entity;

use DateTime;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialTranslation;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class TestimonialTranslationTest extends SuluTestCase
{
    private ObjectProphecy $testimonial;
    private TestimonialTranslation $translation;
    private string $testString = "Lorem ipsum dolor sit amet, ...";

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        $this->testimonial       = $this->prophesize(Testimonial::class);
        $this->translation = new TestimonialTranslation($this->testimonial->reveal(), 'de');
    }

    public function testTestimonial(): void
    {
        $this->assertSame($this->testimonial->reveal(), $this->translation->getTestimonial());
    }

    public function testLocale(): void
    {
        $this->assertSame('de', $this->translation->getLocale());
    }

    public function testTitle(): void
    {
        $this->assertNull($this->translation->getTitle());
        $this->assertSame($this->translation, $this->translation->setTitle($this->testString));
        $this->assertSame($this->testString, $this->translation->getTitle());
    }

    public function testText(): void
    {
        $this->assertNull($this->translation->getText());
        $this->assertSame($this->translation, $this->translation->setText($this->testString));
        $this->assertSame($this->testString, $this->translation->getText());
    }

    public function testPublished(): void
    {
        $this->assertFalse($this->translation->isPublished());
        $this->assertSame($this->translation, $this->translation->setPublished(true));
        $this->assertTrue($this->translation->isPublished());
        $this->assertSame($this->translation, $this->translation->setPublished(false));
        $this->assertFalse($this->translation->isPublished());
    }

    public function testPublishedAt(): void
    {
        $this->assertNull($this->translation->getPublishedAt());
        $this->assertSame($this->translation, $this->translation->setPublished(true));
        $this->assertNotNull($this->translation->getPublishedAt());
        $this->assertSame(DateTime::class, get_class($this->translation->getPublishedAt()));
        $this->assertSame($this->translation, $this->translation->setPublished(false));
        $this->assertNull($this->translation->getPublishedAt());
    }

    public function testRoutePath(): void
    {
        $testRoutePath = 'testimonials/testimonial-100';
        $this->assertEmpty($this->translation->getRoutePath());
        $this->assertSame($this->translation, $this->translation->setRoutePath($testRoutePath));
        $this->assertSame($testRoutePath, $this->translation->getRoutePath());
    }

}
