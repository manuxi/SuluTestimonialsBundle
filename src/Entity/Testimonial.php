<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;

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
    public const LIST_KEY = 'testimonials_list';
    public const LIST_KEY_PUBLISHED = 'testimonials_published';
    public const SECURITY_CONTEXT = 'sulu.testimonials.testimonials';
    public const TEMPLATE_TYPE = 'testimonial';

    private ?int $id = null;

    public function __construct()
    {
        $this->initializeDimensionContents();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function createDimensionContent(): DimensionContentInterface
    {
        return new TestimonialDimensionContent($this);
    }
}