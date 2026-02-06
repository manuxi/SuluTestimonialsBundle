<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Testimonial entity.
 *
 * ORM mapping is defined in Resources/config/doctrine/Testimonial.orm.xml
 */
class Testimonial implements ContentRichEntityInterface
{
    /**
     * @phpstan-use ContentRichEntityTrait<TestimonialDimensionContent>
     */
    use ContentRichEntityTrait;

    public const RESOURCE_KEY = 'testimonials';
    public const FORM_KEY = 'testimonial';
    public const LIST_KEY = 'testimonials';
    public const LIST_KEY_PUBLISHED = 'testimonials_published';
    public const SECURITY_CONTEXT = 'sulu.testimonials.testimonials';
    public const TEMPLATE_TYPE = 'testimonial';

    protected string $uuid;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
        $this->initializeDimensionContents();
    }

    public function getId(): string
    {
        return $this->uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function createDimensionContent(): DimensionContentInterface
    {
        return new TestimonialDimensionContent($this);
    }
}