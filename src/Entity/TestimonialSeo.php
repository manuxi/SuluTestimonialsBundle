<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\SeoInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\SeoTranslatableInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\SeoTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\SeoTranslatableTrait;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialSeoRepository;

#[ORM\Entity(repositoryClass: TestimonialSeoRepository::class)]
#[ORM\Table(name: 'app_testimonial_seo')]
class TestimonialSeo implements SeoInterface, SeoTranslatableInterface
{
    use SeoTrait;
    use SeoTranslatableTrait;

    #[Serializer\Exclude]
    #[ORM\OneToOne(inversedBy: 'testimonialSeo', targetEntity: Testimonial::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'testimonial_id', referencedColumnName: "id", nullable: false)]
    private ?Testimonial $testimonial = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'testimonialSeo', targetEntity: TestimonialSeoTranslation::class, cascade: ['all'], indexBy: 'locale')]
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
     * @return TestimonialSeoTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?TestimonialSeoTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): TestimonialSeoTranslation
    {
        $translation = new TestimonialSeoTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }
}
