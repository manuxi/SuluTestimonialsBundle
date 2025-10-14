<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\AuditableInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\AuditableTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\ShowContactTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\ShowDateTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\ShowOrganisationTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\UrlTrait;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialTranslationRepository;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\ImageTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\PublishedTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\RouteTrait;

#[ORM\Entity(repositoryClass: TestimonialTranslationRepository::class)]
#[ORM\Table(name: 'app_testimonial_translation')]
class TestimonialTranslation implements AuditableInterface
{
    use AuditableTrait;
    use ShowContactTrait;
    use ShowOrganisationTrait;
    use ShowDateTrait;
    use PublishedTrait;
    use RouteTrait;
    use ImageTrait;
    use UrlTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 5)]
    private string $locale;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Testimonial::class, inversedBy: 'translations')]
        #[ORM\JoinColumn(nullable: false)]
        private readonly Testimonial $testimonial,
        string $locale
    ){
        $this->locale = $locale;
    }

    public function __clone(){
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTestimonial(): Testimonial
    {
        return $this->testimonial;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

}
