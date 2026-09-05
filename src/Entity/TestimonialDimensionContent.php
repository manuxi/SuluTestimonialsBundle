<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Content\Domain\Model\AuditableInterface;
use Sulu\Content\Domain\Model\AuditableTrait;
use Sulu\Content\Domain\Model\AuthorInterface;
use Sulu\Content\Domain\Model\AuthorTrait;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\DimensionContentTrait;
use Sulu\Content\Domain\Model\ExcerptInterface;
use Sulu\Content\Domain\Model\ExcerptTrait;
use Sulu\Content\Domain\Model\RoutableInterface;
use Sulu\Content\Domain\Model\RoutableTrait;
use Sulu\Content\Domain\Model\SeoInterface;
use Sulu\Content\Domain\Model\SeoTrait;
use Sulu\Content\Domain\Model\ShadowInterface;
use Sulu\Content\Domain\Model\ShadowTrait;
use Sulu\Content\Domain\Model\TaxonomyInterface;
use Sulu\Content\Domain\Model\TaxonomyTrait;
use Sulu\Content\Domain\Model\TemplateInterface;
use Sulu\Content\Domain\Model\TemplateTrait;
use Sulu\Content\Domain\Model\WebspaceInterface;
use Sulu\Content\Domain\Model\WebspaceTrait;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * @implements DimensionContentInterface<Testimonial>
 */
class TestimonialDimensionContent implements DimensionContentInterface, ExcerptInterface, SeoInterface, TemplateInterface, RoutableInterface, WorkflowInterface, AuthorInterface, WebspaceInterface, ShadowInterface, AuditableInterface, TaxonomyInterface
{
    use AuthorTrait;
    use DimensionContentTrait;
    use ExcerptTrait;
    use TaxonomyTrait;
    use RoutableTrait;
    use SeoTrait;
    use ShadowTrait;
    use TemplateTrait {
        TemplateTrait::setTemplateData as parentSetTemplateData;
    }
    use WebspaceTrait;
    use WorkflowTrait;
    use AuditableTrait;

    protected ?int $id = null;

    #[Ignore]
    protected Testimonial $testimonial;

    protected ?string $title = null;
    protected ?string $text = null;
    protected ?string $rating = null;
    protected ?string $source = null;
    protected ?bool $showOrganisation = false;
    protected ?bool $showContact = false;
    protected ?bool $showDate = false;
    protected ?\DateTimeImmutable $date = null;
    protected ?MediaInterface $image = null;
    protected ?ContactInterface $contact = null;
    protected ?int $contactId = null;
    protected ?string $website = null;

    public function __construct(Testimonial $testimonial)
    {
        $this->testimonial = $testimonial;
        $this->created = new \DateTimeImmutable();
        $this->changed = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResource(): ContentRichEntityInterface
    {
        return $this->testimonial;
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

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;
        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getShowOrganisation(): ?bool
    {
        return $this->showOrganisation;
    }

    public function setShowOrganisation(?bool $showOrganisation): self
    {
        $this->showOrganisation = $showOrganisation;
        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getShowContact(): ?bool
    {
        return $this->showContact;
    }

    public function setShowContact(?bool $showContact): self
    {
        $this->showContact = $showContact;
        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getShowDate(): ?bool
    {
        return $this->showDate;
    }

    public function setShowDate(?bool $showDate): self
    {
        $this->showDate = $showDate;
        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): self
    {
        $this->date = $date;
        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    public function setImage(?MediaInterface $image): self
    {
        $this->image = $image;
        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getContact(): ?ContactInterface
    {
        return $this->contact;
    }

    public function setContact(?ContactInterface $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    #[Serializer\Groups(['default', 'admin', 'fullTestimonial', 'partialTestimonial'])]
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;
        return $this;
    }

    public function copyAttributesFrom(DimensionContentInterface $dimensionContent): void
    {
        if (!$dimensionContent instanceof self) {
            return;
        }

        $this->title = $dimensionContent->title;
        $this->text = $dimensionContent->text;
        $this->rating = $dimensionContent->rating;
        $this->source = $dimensionContent->source;
        $this->showOrganisation = $dimensionContent->showOrganisation;
        $this->showContact = $dimensionContent->showContact;
        $this->showDate = $dimensionContent->showDate;
        $this->date = $dimensionContent->date;
        $this->image = $dimensionContent->image;
        $this->contact = $dimensionContent->contact;
        $this->website = $dimensionContent->website;
    }

    public function setTemplateData(array $templateData): void
    {
        $this->parentSetTemplateData($templateData);

        // Map fields from templateData to properties
        if (isset($templateData['title']))
            $this->title = $templateData['title'];
        if (isset($templateData['text']))
            $this->text = $templateData['text'];
        if (isset($templateData['rating']))
            $this->rating = $templateData['rating'];
        if (isset($templateData['source']))
            $this->source = $templateData['source'];
        if (isset($templateData['showOrganisation']))
            $this->showOrganisation = (bool) $templateData['showOrganisation'];
        if (isset($templateData['showContact']))
            $this->showContact = (bool) $templateData['showContact'];
        if (isset($templateData['showDate']))
            $this->showDate = (bool) $templateData['showDate'];
        if (isset($templateData['website']))
            $this->website = $templateData['website'];

        if (isset($templateData['date']) && $templateData['date']) {
            if ($templateData['date'] instanceof \DateTimeImmutable) {
                $this->date = $templateData['date'];
            } elseif (is_string($templateData['date'])) {
                try {
                    $this->date = new \DateTimeImmutable($templateData['date']);
                } catch (\Exception $e) {
                }
            }
        }

        if (isset($templateData['image']) && $templateData['image'] instanceof MediaInterface) {
            $this->image = $templateData['image'];
        }

        if (isset($templateData['contact']) && $templateData['contact'] instanceof ContactInterface) {
            $this->contact = $templateData['contact'];
        }

        if (array_key_exists('contact', $templateData) &&
            (is_int($templateData['contact']) || is_string($templateData['contact']))
        ) {
            $this->contactId = (int) $templateData['contact'];
        }
    }

    public static function getTemplateType(): string
    {
        return Testimonial::TEMPLATE_TYPE;
    }

    public static function getResourceKey(): string
    {
        return Testimonial::RESOURCE_KEY;
    }
}
