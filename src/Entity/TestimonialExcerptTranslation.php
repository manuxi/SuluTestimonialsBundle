<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Manuxi\SuluSharedToolsBundle\Entity\Abstracts\Entity\AbstractExcerptTranslation;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialExcerptTranslationRepository;

#[ORM\Entity(repositoryClass: TestimonialExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_testimonial_excerpt_translation')]
class TestimonialExcerptTranslation extends AbstractExcerptTranslation implements ExcerptTranslationInterface
{
    #[JoinTable(name: 'app_testimonial_excerpt_categories')]
    protected ?Collection $categories = null;

    #[JoinTable(name: 'app_testimonial_excerpt_tags')]
    protected ?Collection $tags = null;

    #[JoinTable(name: 'app_testimonial_excerpt_icons')]
    protected ?Collection $icons = null;

    #[JoinTable(name: 'app_testimonial_excerpt_images')]
    protected ?Collection $images = null;

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
