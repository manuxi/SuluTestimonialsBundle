<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Application\Mapper;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class TestimonialMapper implements TestimonialMapperInterface
{
    public function __construct(
        private readonly ContentManagerInterface $contentManager,
    ) {
    }

    public function mapTestimonialData(Testimonial $testimonial, array $data): void
    {
        $locale = $data['locale'] ?? null;
        $stage = $data['stage'] ?? DimensionContentInterface::STAGE_DRAFT;

        $dimensionAttributes = [
            'locale' => $locale,
            'stage' => $stage,
        ];

        $this->contentManager->persist($testimonial, $data, $dimensionAttributes);
    }
}