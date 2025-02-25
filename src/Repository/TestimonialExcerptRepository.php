<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialExcerpt;

/**
 * @method TestimonialExcerpt|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestimonialExcerpt|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestimonialExcerpt[]    findAll()
 * @method TestimonialExcerpt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Event>
 */
class TestimonialExcerptRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestimonialExcerpt::class);
    }

    public function create(string $locale): TestimonialExcerpt
    {
        $testimonialExcerpt = new TestimonialExcerpt();
        $testimonialExcerpt->setLocale($locale);

        return $testimonialExcerpt;
    }

    public function remove(int $id): void
    {
        /** @var object $testimonialExcerpt */
        $testimonialExcerpt = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($testimonialExcerpt);
        $this->getEntityManager()->flush();
    }

    public function save(TestimonialExcerpt $testimonialExcerpt): TestimonialExcerpt
    {
        $this->getEntityManager()->persist($testimonialExcerpt);
        $this->getEntityManager()->flush();
        return $testimonialExcerpt;
    }

    public function findById(int $id, string $locale): ?TestimonialExcerpt
    {
        $testimonialExcerpt = $this->find($id);
        if (!$testimonialExcerpt) {
            return null;
        }

        $testimonialExcerpt->setLocale($locale);

        return $testimonialExcerpt;
    }

}
