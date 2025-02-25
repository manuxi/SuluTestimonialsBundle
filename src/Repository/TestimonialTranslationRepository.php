<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestimonialTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestimonialTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestimonialTranslation[]    findAll()
 * @method TestimonialTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<TestimonialTranslation>
 */
class TestimonialTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestimonialTranslation::class);
    }

    public function findMissingLocaleByIds(array $ids, string $missingLocale, int $countLocales)
    {
        $query = $this->createQueryBuilder('et')
            ->addCriteria($this->createIdsInCriteria($ids))
            ->groupby('et.testimonial')
            ->having('testimonialCount < :countLocales')
            ->setParameter('countLocales', $countLocales)
            ->andHaving('et.locale = :locale')
            ->setParameter('locale', $missingLocale)
            ->select('IDENTITY(et.testimonial) as testimonial, et.locale, count(et.testimonial) as testimonialCount')
            ->getQuery()
        ;
//        dump($query->getSQL());
        return $query->getResult();
    }

    private function createIdsInCriteria(array $ids): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->in('testimonial', $ids))
            ;
    }

}
