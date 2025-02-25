<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\Type;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SingleTestimonialSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('single_testimonial_selection');
    }

    public function getContentData(PropertyInterface $property): ?Testimonial
    {
        $id = $property->getValue();

        if (empty($id)) {
            return null;
        }

        return $this->entityManager->getRepository(Testimonial::class)->find($id);
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
