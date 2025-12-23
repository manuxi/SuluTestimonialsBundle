<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Entity;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialsSettings;
use PHPUnit\Framework\TestCase;

class TestimonialsSettingsTest extends TestCase
{
    private $settings;

    protected function setUp(): void
    {
        $this->settings = new TestimonialsSettings();
    }

    public function testGetIdDefault(): void
    {
        $this->assertNull($this->settings->getId());
    }

    public function testToggleHeader(): void
    {
        $this->assertNull($this->settings->getToggleHeader());
        $this->settings->setToggleHeader(true);
        $this->assertTrue($this->settings->getToggleHeader());
        $this->settings->setToggleHeader(false);
        $this->assertFalse($this->settings->getToggleHeader());
    }

    public function testToggleHero(): void
    {
        $this->assertNull($this->settings->getToggleHero());
        $this->settings->setToggleHero(true);
        $this->assertTrue($this->settings->getToggleHero());
    }

    public function testToggleBreadcrumbs(): void
    {
        $this->assertNull($this->settings->getToggleBreadcrumbs());
        $this->settings->setToggleBreadcrumbs(true);
        $this->assertTrue($this->settings->getToggleBreadcrumbs());
    }

    public function testPageTestimonials(): void
    {
        $this->assertNull($this->settings->getPageTestimonials());
        $this->settings->setPageTestimonials('Test Page');
        $this->assertSame('Test Page', $this->settings->getPageTestimonials());
    }

    public function testConstants(): void
    {
        $this->assertSame('testimonials_settings', TestimonialsSettings::RESOURCE_KEY);
        $this->assertSame('testimonials_config', TestimonialsSettings::FORM_KEY);
        $this->assertSame('sulu.testimonials.settings', TestimonialsSettings::SECURITY_CONTEXT);
    }

    public function testAuditableTrait(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->settings->getCreated());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->settings->getChanged());
    }
}
