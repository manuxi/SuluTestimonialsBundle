<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\Type;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class TestimonialsSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('testimonial_selection', []);
    }

    /**
     * @param PropertyInterface $property
     * @return Testimonial[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();

        if (empty($ids)) {
            return [];
        }

        $testimonial = $this->entityManager->getRepository(Testimonial::class)->findBy(['id' => $ids]);

        $idPositions = \array_flip($ids);
        \usort($testimonial, static function (Testimonial $a, Testimonial $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });

        return $testimonial;
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
