<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Repository;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialExcerptTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestimonialExcerptTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestimonialExcerptTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestimonialExcerptTranslation[]    findAll()
 * @method TestimonialExcerptTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<TestimonialTranslation>
 */
class TestimonialExcerptTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestimonialExcerptTranslation::class);
    }
}
