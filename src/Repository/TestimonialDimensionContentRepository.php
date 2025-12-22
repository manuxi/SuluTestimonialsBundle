<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;

/**
 * @extends ServiceEntityRepository<TestimonialDimensionContent>
 */
class TestimonialDimensionContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestimonialDimensionContent::class);
    }

    public function findMissingLocaleByIds(array $ids, string $locale, int $localesCount = 0): array
    {
        if (empty($ids)) {
            return [];
        }

        $qb = $this->createQueryBuilder('dimensionContent');
        $qb->select('identity(dimensionContent.testimonial) as testimonial')
            ->where($qb->expr()->in('dimensionContent.testimonial', $ids))
            ->andWhere('dimensionContent.locale = :locale')
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getArrayResult();
    }
    public function load(string $id, array $options = []): ?TestimonialDimensionContent
    {
        $locale = $options['locale'] ?? null;
        if (!$locale) {
            return null;
        }

        $qb = $this->createQueryBuilder('dimensionContent');
        $qb->where('dimensionContent.testimonial = :id')
            ->andWhere('dimensionContent.locale = :locale')
            ->setParameter('id', $id)
            ->setParameter('locale', $locale)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
