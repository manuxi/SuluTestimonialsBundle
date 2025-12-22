<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\Type;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialDimensionContentRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SingleTestimonialSelection extends SimpleContentType
{
    public function __construct(
        private readonly TestimonialDimensionContentRepository $repository
    ) {
        parent::__construct('single_testimonial_selection');
    }

    public function getContentData(PropertyInterface $property): ?TestimonialDimensionContent
    {
        $id = $property->getValue();
        $locale = $property->getStructure()->getLanguageCode();

        if (empty($id)) {
            return null;
        }

        return $this->repository->load($id, ['locale' => $locale]);
    }

    /**
     * @param PropertyInterface $property
     * @return array
     */
    public function getViewData(PropertyInterface $property): array
    {
        return [
            'id' => $property->getValue(),
        ];
    }
}
