<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\SeoTranslationInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\SeoTranslationTrait;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialSeoTranslationRepository;

#[ORM\Entity(repositoryClass: TestimonialSeoTranslationRepository::class)]
#[ORM\Table(name: 'app_testimonial_seo_translation')]
class TestimonialSeoTranslation implements SeoTranslationInterface
{
    use SeoTranslationTrait;

    #[ORM\ManyToOne(targetEntity: TestimonialSeo::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private TestimonialSeo $testimonialSeo;

    public function __construct(TestimonialSeo $testimonialSeo, string $locale)
    {
        $this->testimonialSeo = $testimonialSeo;
        $this->setLocale($locale);
    }

    public function getSeo(): TestimonialSeo
    {
        return $this->testimonialSeo;
    }

}
