<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\Type;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialDimensionContentRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class TestimonialsSelection extends SimpleContentType
{
    public function __construct(
        private readonly TestimonialDimensionContentRepository $repository
    ) {
        parent::__construct('testimonial_selection', []);
    }

    /**
     * @param PropertyInterface $property
     * @return TestimonialDimensionContent[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        $locale = $property->getStructure()->getLanguageCode();

        if (empty($ids)) {
            return [];
        }

        $items = [];
        foreach ($ids as $id) {
            $item = $this->repository->load($id, ['locale' => $locale]);
            if ($item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param PropertyInterface $property
     * @return array
     */
    public function getViewData(PropertyInterface $property): array
    {
        return [
            'ids' => $property->getValue(),
        ];
    }
}
