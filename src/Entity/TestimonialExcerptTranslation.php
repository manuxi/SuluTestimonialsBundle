<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\ExcerptTranslationTrait;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialExcerptTranslationRepository;

#[ORM\Entity(repositoryClass: TestimonialExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_testimonial_excerpt_translation')]
class TestimonialExcerptTranslation implements ExcerptTranslationInterface
{
    use ExcerptTranslationTrait;

    #[ORM\ManyToOne(targetEntity: TestimonialExcerpt::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private TestimonialExcerpt $testimonialExcerpt;

    public function __construct(TestimonialExcerpt $testimonialExcerpt, string $locale)
    {
        $this->testimonialExcerpt = $testimonialExcerpt;
        $this->setLocale($locale);
        $this->initExcerptTranslationTrait();
    }

    public function getExcerpt(): TestimonialExcerpt
    {
        return $this->testimonialExcerpt;
    }
}
