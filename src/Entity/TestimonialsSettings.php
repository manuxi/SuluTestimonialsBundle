<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * TestimonialsSettings entity.
 *
 * ORM mapping is defined in Resources/config/doctrine/TestimonialsSettings.orm.xml
 */
class TestimonialsSettings implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'testimonials_settings';
    public const FORM_KEY = 'testimonials_config';
    public const SECURITY_CONTEXT = 'sulu.testimonials.settings';

    private ?int $id = null;

    private ?bool $toggleHeader = null;

    private ?bool $toggleHero = null;

    private ?bool $toggleBreadcrumbs = null;

    private ?string $pageTestimonials = null;

    public function __construct()
    {
        $this->created = new \DateTimeImmutable();
        $this->changed = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToggleHeader(): ?bool
    {
        return $this->toggleHeader;
    }

    public function setToggleHeader(?bool $toggleHeader): void
    {
        $this->toggleHeader = $toggleHeader;
    }

    public function getToggleHero(): ?bool
    {
        return $this->toggleHero;
    }

    public function setToggleHero(?bool $toggleHero): void
    {
        $this->toggleHero = $toggleHero;
    }

    public function getToggleBreadcrumbs(): ?bool
    {
        return $this->toggleBreadcrumbs;
    }

    public function setToggleBreadcrumbs(?bool $toggleBreadcrumbs): void
    {
        $this->toggleBreadcrumbs = $toggleBreadcrumbs;
    }

    public function getPageTestimonials(): ?string
    {
        return $this->pageTestimonials;
    }

    public function setPageTestimonials(?string $pageTestimonials): void
    {
        $this->pageTestimonials = $pageTestimonials;
    }
}