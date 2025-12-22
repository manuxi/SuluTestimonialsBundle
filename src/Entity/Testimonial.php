<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;

#[ORM\Entity(repositoryClass: TestimonialRepository::class)]
#[ORM\Table(name: 'app_testimonial')]
class Testimonial implements ContentRichEntityInterface
{
    /**
     * @phpstan-use ContentRichEntityTrait<TestimonialDimensionContent>
     */
    use ContentRichEntityTrait;

    public const RESOURCE_KEY = 'testimonials';
    public const FORM_KEY = 'testimonial';
    public const LIST_KEY = 'testimonials';
    public const SECURITY_CONTEXT = 'sulu.testimonials.testimonials';
    public const TEMPLATE_TYPE = 'testimonial';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
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
