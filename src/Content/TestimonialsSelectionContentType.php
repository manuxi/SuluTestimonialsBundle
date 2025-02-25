<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class TestimonialsSelectionContentType extends SimpleContentType
{
    private TestimonialRepository $testimonialRepository;

    public function __construct(TestimonialRepository $testimonialRepository)
    {
        parent::__construct('testimonial_selection');

        $this->testimonialRepository = $testimonialRepository;
    }

    /**
     * @param PropertyInterface $property
     * @return Testimonial[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        $locale = $property->getStructure()->getLanguageCode();

        $testimonials = [];
        foreach ($ids ?: [] as $id) {
            $testimonial = $this->testimonialRepository->findById((int) $id, $locale);
            if ($testimonial && $testimonial->isPublished()) {
                $testimonials[] = $testimonial;
            }
        }
        return $testimonials;
    }

    public function getViewData(PropertyInterface $property): mixed
    {
        return $property->getValue();
    }
}
