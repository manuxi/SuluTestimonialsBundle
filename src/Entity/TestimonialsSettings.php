<?php

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

#[ORM\Entity()]
#[ORM\Table(name: 'app_testimonial_settings')]
class TestimonialsSettings implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'testimonials_settings';
    public const FORM_KEY = 'testimonials_config';
    public const SECURITY_CONTEXT = 'sulu.testimonials.settings';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleHeader = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleHero = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleBreadcrumbs = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageTestimonials = null;

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