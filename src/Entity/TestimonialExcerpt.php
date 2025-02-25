<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\ExcerptInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\ExcerptTranslatableInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\ExcerptTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\ExcerptTranslatableTrait;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialExcerptRepository;

#[ORM\Entity(repositoryClass: TestimonialExcerptRepository::class)]
#[ORM\Table(name: 'app_testimonial_excerpt')]
class TestimonialExcerpt implements ExcerptInterface, ExcerptTranslatableInterface
{
    use ExcerptTrait;
    use ExcerptTranslatableTrait;

    #[Serializer\Exclude]
    #[ORM\OneToOne(inversedBy: 'testimonialExcerpt', targetEntity: Testimonial::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'testimonial_id', referencedColumnName: "id", nullable: false)]
    private ?Testimonial $testimonial = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'testimonialExcerpt', targetEntity: TestimonialExcerptTranslation::class, cascade: ['all'], indexBy: 'locale')]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getTestimonial(): ?Testimonial
    {
        return $this->testimonial;
    }

    public function setTestimonial(Testimonial $testimonial): self
    {
        $this->testimonial = $testimonial;
        return $this;
    }

    /**
     * @return TestimonialExcerptTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?TestimonialExcerptTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): TestimonialExcerptTranslation
    {
        $translation = new TestimonialExcerptTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

}
