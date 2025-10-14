<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Entity;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialExcerpt;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialSeo;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialTranslation;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class TestimonialTest extends SuluTestCase
{
    private Testimonial $entity;
    private string $testString = "Lorem ipsum dolor sit amet, ...";
    private string $testRating = "3";

    protected function setUp(): void
    {
        $this->entity = new Testimonial();
        $this->entity->setLocale('de');
    }

    public function testPublished(): void
    {
        $this->assertNull($this->entity->isPublished());
        $this->assertSame($this->entity, $this->entity->setPublished(true));
        $this->assertTrue($this->entity->isPublished());
        $this->assertSame($this->entity, $this->entity->setPublished(false));
        $this->assertFalse($this->entity->isPublished());
    }

    public function testPublishedState(): void
    {
        $this->assertNull($this->entity->getPublishedState());
        $this->assertSame($this->entity, $this->entity->setPublished(true));
        $this->assertEquals(1, $this->entity->getPublishedState());
        $this->assertSame($this->entity, $this->entity->setPublished(false));
        $this->assertEquals(0, $this->entity->getPublishedState());
    }

    public function testTitle(): void
    {
        $this->assertNull($this->entity->getTitle());
        $this->assertSame($this->entity, $this->entity->setTitle($this->testString));
        $this->assertSame($this->testString, $this->entity->getTitle());

        $this->assertInstanceOf(TestimonialTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getTitle());
    }

    public function testText(): void
    {
        $this->assertNull($this->entity->getText());
        $this->assertSame($this->entity, $this->entity->setText($this->testString));
        $this->assertSame($this->testString, $this->entity->getText());

        $this->assertInstanceOf(TestimonialTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getText());
    }

    public function testSource(): void
    {
        $this->assertNull($this->entity->getSource());
        $this->assertSame($this->entity, $this->entity->setSource($this->testString));
        $this->assertSame($this->testString, $this->entity->getSource());

    }

    public function testRating(): void
    {
        $this->assertNull($this->entity->getRating());
        $this->assertSame($this->entity, $this->entity->setRating($this->testRating));
        $this->assertSame($this->testRating, $this->entity->getRating());

    }

    public function testShowOrganisation(): void
    {
        $this->assertFalse($this->entity->getShowOrganisation());
        $this->assertSame($this->entity, $this->entity->setShowOrganisation(true));
        $this->assertSame(true, $this->entity->getShowOrganisation());
        $this->assertSame($this->entity, $this->entity->setShowOrganisation(false));
        $this->assertSame(false, $this->entity->getShowOrganisation());
    }

    public function testLocale(): void
    {
        $this->assertSame('de', $this->entity->getLocale());
        $this->assertSame($this->entity, $this->entity->setLocale('en'));
        $this->assertSame('en', $this->entity->getLocale());
    }

    public function testTestimonialSeo(): void
    {
        $testimonialSeo = $this->prophesize(TestimonialSeo::class);
        $testimonialSeo->getId()->willReturn(42);

        $this->assertInstanceOf(TestimonialSeo::class, $this->entity->getSeo());
        $this->assertNull($this->entity->getSeo()->getId());
        $this->assertSame($this->entity, $this->entity->setSeo($testimonialSeo->reveal()));
        $this->assertSame($testimonialSeo->reveal(), $this->entity->getSeo());
    }

    public function testTestimonialExcerpt(): void
    {
        $testimonialExcerpt = $this->prophesize(TestimonialExcerpt::class);
        $testimonialExcerpt->getId()->willReturn(42);

        $this->assertInstanceOf(TestimonialExcerpt::class, $this->entity->getExcerpt());
        $this->assertNull($this->entity->getExcerpt()->getId());
        $this->assertSame($this->entity, $this->entity->setExcerpt($testimonialExcerpt->reveal()));
        $this->assertSame($testimonialExcerpt->reveal(), $this->entity->getExcerpt());
    }

    public function testExt(): void
    {
        $ext = $this->entity->getExt();
        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(TestimonialSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->assertArrayHasKey('excerpt', $ext);
        $this->assertInstanceOf(TestimonialExcerpt::class, $ext['excerpt']);
        $this->assertNull($ext['excerpt']->getId());

        $this->entity->addExt('foo', new TestimonialSeo());
        $this->entity->addExt('bar', new TestimonialExcerpt());
        $ext = $this->entity->getExt();

        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(TestimonialSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->assertArrayHasKey('excerpt', $ext);
        $this->assertInstanceOf(TestimonialExcerpt::class, $ext['excerpt']);
        $this->assertNull($ext['excerpt']->getId());

        $this->assertArrayHasKey('foo', $ext);
        $this->assertInstanceOf(TestimonialSeo::class, $ext['foo']);
        $this->assertNull($ext['foo']->getId());

        $this->assertArrayHasKey('bar', $ext);
        $this->assertInstanceOf(TestimonialExcerpt::class, $ext['bar']);
        $this->assertNull($ext['bar']->getId());

        $this->assertTrue($this->entity->hasExt('seo'));
        $this->assertTrue($this->entity->hasExt('excerpt'));
        $this->assertTrue($this->entity->hasExt('foo'));
        $this->assertTrue($this->entity->hasExt('bar'));

        $this->entity->setExt(['and' => 'now', 'something' => 'special']);
        $ext = $this->entity->getExt();
        $this->assertArrayNotHasKey('seo', $ext);
        $this->assertArrayNotHasKey('excerpt', $ext);
        $this->assertArrayNotHasKey('foo', $ext);
        $this->assertArrayNotHasKey('bar', $ext);
        $this->assertArrayHasKey('and', $ext);
        $this->assertArrayHasKey('something', $ext);
        $this->assertTrue($this->entity->hasExt('and'));
        $this->assertTrue($this->entity->hasExt('something'));
        $this->assertTrue('now' === $ext['and']);
        $this->assertTrue('special' === $ext['something']);
    }

    public function testPropagateLocale(): void
    {
        $this->assertSame($this->entity->getExt()['seo']->getLocale(), 'de');
        $this->assertSame($this->entity->getExt()['excerpt']->getLocale(), 'de');
        $this->entity->setLocale('en');
        $this->assertSame($this->entity->getExt()['seo']->getLocale(), 'en');
        $this->assertSame($this->entity->getExt()['excerpt']->getLocale(), 'en');
    }

    public function testTranslations(): void
    {
        $this->assertSame($this->entity->getTranslations(), []);
        $this->entity->setText($this->testString);
        $this->assertNotSame($this->entity->getTranslations(), []);
        $this->assertArrayHasKey('de', $this->entity->getTranslations());
        $this->assertArrayNotHasKey('en', $this->entity->getTranslations());
        $this->assertSame($this->entity->getText(), $this->testString);

        $this->entity->setLocale('en');
        $this->entity->setText($this->testString);
        $this->assertArrayHasKey('de', $this->entity->getTranslations());
        $this->assertArrayHasKey('en', $this->entity->getTranslations());
        $this->assertSame($this->entity->getText(), $this->testString);
        //No need to test more, it's s already done...
    }
}
