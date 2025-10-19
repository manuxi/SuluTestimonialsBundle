<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\AuditableTranslatableInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\SearchableInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\AuditableTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ContactTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\DateTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ImageTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\PublishedTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\RoutePathTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ShowContactTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ShowDateTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ShowOrganisationTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\UrlTranslatableTrait;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;

#[ORM\Entity(repositoryClass: TestimonialRepository::class)]
#[ORM\Table(name: 'app_testimonial')]
class Testimonial implements AuditableTranslatableInterface, SearchableInterface
{
    use AuditableTranslatableTrait;
    use PublishedTranslatableTrait;
    use RoutePathTranslatableTrait;
    use UrlTranslatableTrait;
    use ImageTranslatableTrait;
    use ContactTrait;
    use ShowContactTranslatableTrait;
    use ShowOrganisationTranslatableTrait;
    use DateTrait;
    use ShowDateTranslatableTrait;
    public const RESOURCE_KEY = 'testimonials';
    public const FORM_KEY = 'testimonial_details';
    public const LIST_KEY = 'testimonials';
    public const SECURITY_CONTEXT = 'sulu.testimonials.testimonials';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Serializer\Exclude]
    #[ORM\OneToOne(mappedBy: 'testimonial', targetEntity: TestimonialSeo::class, cascade: ['persist', 'remove'])]
    private ?TestimonialSeo $testimonialSeo = null;

    #[Serializer\Exclude]
    #[ORM\OneToOne(mappedBy: 'testimonial', targetEntity: TestimonialExcerpt::class, cascade: ['persist', 'remove'])]
    private ?TestimonialExcerpt $testimonialExcerpt = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'testimonial', targetEntity: TestimonialTranslation::class, cascade: ['all'], fetch: 'EXTRA_LAZY', indexBy: 'locale')]
    private Collection $translations;

    #[ORM\Column(type: Types::STRING)]
    private ?string $rating = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $source = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showOrganisation = null;

    private string $locale = 'de';

    private array $ext = [];

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->initExt();
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Serializer\VirtualProperty(name: 'title')]
    public function getTitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getTitle();
    }

    public function setTitle(string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setTitle($title);

        return $this;
    }

    #[Serializer\VirtualProperty(name: 'text')]
    public function getText(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getText();
    }

    public function setText(string $text): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setText($text);

        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(string $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getShowOrganisation(): bool
    {
        return $this->showOrganisation ?? false;
    }

    public function setShowOrganisation(bool $showOrganisation): self
    {
        $this->showOrganisation = $showOrganisation;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        $this->propagateLocale($locale);

        return $this;
    }

    /**
     * @return TestimonialTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?TestimonialTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): TestimonialTranslation
    {
        $translation = new TestimonialTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

    #[Serializer\VirtualProperty(name: 'availableLocales')]
    public function getAvailableLocales(): array
    {
        return \array_values($this->translations->getKeys());
    }

    /**
     * @todo implement object cloning/copy
     *
     * @return $this|null
     */
    public function copy(Testimonial $copy): ?static
    {
        if ($currentTranslation = $this->getTranslation($this->getLocale())) {
            $newTranslation = clone $currentTranslation;
            $copy->setTranslation($newTranslation);

            // copy ext also...
            foreach ($this->ext as $key => $translatable) {
                $copy->addExt($key, clone $translatable);
            }
        }

        return $copy;
    }

    public function copyToLocale(string $locale): self
    {
        if ($currentTranslation = $this->getTranslation($this->getLocale())) {
            $newTranslation = clone $currentTranslation;
            $newTranslation->setLocale($locale);
            $this->translations->set($locale, $newTranslation);

            // copy ext also...
            foreach ($this->ext as $translatable) {
                $translatable->copyToLocale($locale);
            }

            $this->setLocale($locale);
        }

        return $this;
    }

    public function getSeo(): TestimonialSeo
    {
        if (!$this->testimonialSeo instanceof TestimonialSeo) {
            $this->testimonialSeo = new TestimonialSeo();
            $this->testimonialSeo->setTestimonial($this);
        }

        return $this->testimonialSeo;
    }

    public function setSeo(?TestimonialSeo $testimonialSeo): self
    {
        $this->testimonialSeo = $testimonialSeo;

        return $this;
    }

    public function getExcerpt(): TestimonialExcerpt
    {
        if (!$this->testimonialExcerpt instanceof TestimonialExcerpt) {
            $this->testimonialExcerpt = new TestimonialExcerpt();
            $this->testimonialExcerpt->setTestimonial($this);
        }

        return $this->testimonialExcerpt;
    }

    public function setExcerpt(?TestimonialExcerpt $testimonialExcerpt): self
    {
        $this->testimonialExcerpt = $testimonialExcerpt;

        return $this;
    }

    #[Serializer\VirtualProperty(name: 'ext')]
    public function getExt(): array
    {
        return $this->ext;
    }

    public function setExt(array $ext): self
    {
        $this->ext = $ext;

        return $this;
    }

    public function addExt(string $key, $value): self
    {
        $this->ext[$key] = $value;

        return $this;
    }

    public function hasExt(string $key): bool
    {
        return \array_key_exists($key, $this->ext);
    }

    private function propagateLocale(string $locale): self
    {
        $testimonialSeo = $this->getSeo();
        $testimonialSeo->setLocale($locale);
        $testimonialExcerpt = $this->getExcerpt();
        $testimonialExcerpt->setLocale($locale);
        $this->initExt();

        return $this;
    }

    private function initExt(): self
    {
        if (!$this->hasExt('seo')) {
            $this->addExt('seo', $this->getSeo());
        }
        if (!$this->hasExt('excerpt')) {
            $this->addExt('excerpt', $this->getExcerpt());
        }

        return $this;
    }
}
