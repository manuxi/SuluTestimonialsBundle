<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Manuxi\SuluSharedToolsBundle\Entity\Abstracts\Entity\AbstractExcerptTranslation;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialExcerptTranslationRepository;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

#[ORM\Entity(repositoryClass: TestimonialExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_testimonial_excerpt_translation')]
class TestimonialExcerptTranslation extends AbstractExcerptTranslation implements ExcerptTranslationInterface
{
    #[ManyToMany(targetEntity: Category::class)]
    #[JoinTable(name: 'app_testimonial_excerpt_categories')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'category_id', referencedColumnName: 'id')]
    protected ?Collection $categories = null;

    #[ManyToMany(targetEntity: TagInterface::class)]
    #[JoinTable(name: 'app_testimonial_excerpt_tags')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    protected ?Collection $tags = null;

    #[ManyToMany(targetEntity: MediaInterface::class)]
    #[JoinTable(name: 'app_testimonial_excerpt_icons')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'icon_id', referencedColumnName: 'id')]
    protected ?Collection $icons = null;

    #[ManyToMany(targetEntity: MediaInterface::class)]
    #[JoinTable(name: 'app_testimonial_excerpt_images')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'image_id', referencedColumnName: 'id')]
    protected ?Collection $images = null;

    #[ORM\ManyToOne(targetEntity: TestimonialExcerpt::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: false)]
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
