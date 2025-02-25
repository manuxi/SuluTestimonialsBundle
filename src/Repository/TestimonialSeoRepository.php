<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Repository;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialSeo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestimonialSeo|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestimonialSeo|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestimonialSeo[]    findAll()
 * @method TestimonialSeo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Event>
 */
class TestimonialSeoRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestimonialSeo::class);
    }

    public function create(string $locale): TestimonialSeo
    {
        $testimonialSeo = new TestimonialSeo();
        $testimonialSeo->setLocale($locale);

        return $testimonialSeo;
    }

    public function remove(int $id): void
    {
        /** @var object $testimonialSeo */
        $testimonialSeo = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($testimonialSeo);
        $this->getEntityManager()->flush();
    }

    public function save(TestimonialSeo $testimonialSeo): TestimonialSeo
    {
        $this->getEntityManager()->persist($testimonialSeo);
        $this->getEntityManager()->flush();
        return $testimonialSeo;
    }

    public function findById(int $id, string $locale): ?TestimonialSeo
    {
        $testimonialSeo = $this->find($id);
        if (!$testimonialSeo) {
            return null;
        }

        $testimonialSeo->setLocale($locale);

        return $testimonialSeo;
    }

}
