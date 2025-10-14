<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

/**
 * @method Testimonial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Testimonial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Testimonial[]    findAll()
 * @method Testimonial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Testimonial>
 */
class TestimonialRepository extends ServiceEntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait {
        findByFilters as protected parentFindByFilters;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testimonial::class);
    }

    public function create(string $locale): Testimonial
    {
        $entity = new Testimonial();
        $entity->setLocale($locale);

        return $entity;
    }

    public function remove(int $id): void
    {
        /** @var object $entity */
        $entity = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function save(Testimonial $entity): Testimonial
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function findById(int $id, string $locale): ?Testimonial
    {
        $entity = $this->find($id);

        if (!$entity) {
            return null;
        }

        $entity->setLocale($locale);

        return $entity;
    }

    public function findAllForSitemap(string $locale, int $limit = null, int $offset = null): array
    {
        $queryBuilder = $this->createQueryBuilder('testimonial')
            ->leftJoin('testimonial.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('translation.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->prepareFilter($queryBuilder, []);

        $testimonials = $queryBuilder->getQuery()->getResult();
        if (!$testimonials) {
            return [];
        }
        return $testimonials;
    }

    public function countForSitemap(string $locale)
    {
        $query = $this->createQueryBuilder('testimonial')
            ->select('count(testimonial)')
            ->leftJoin('testimonial.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale);
        return $query->getQuery()->getSingleScalarResult();
    }

    protected function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {

    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $locale
     * @param mixed[] $options
     *
     * @return string[]
     */
    protected function append(QueryBuilder $queryBuilder, string $alias, string $locale, $options = []): array
    {
        //$queryBuilder->andWhere($alias . '.translation.published = true');
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->andWhere('translation.published = :published');
        $queryBuilder->setParameter('published', true);
        return [];
    }

    public function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias)
    {
        return $alias . '.category';
        //$queryBuilder->addSelect($alias.'.category');
    }

    protected function appendSortByJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
    }

    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $entities = $this->getPublishedTestimonials($filters, $locale, $page, $pageSize, $limit, $options);

        return \array_map(
            function (Testimonial $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }

    public function hasNextPage(array $filters, ?int $page, ?int $pageSize, ?int $limit, string $locale, array $options = []): bool
    {
        $pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;
        $totalArticles = $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->leftJoin('n.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getSingleScalarResult();

        if ((int)($limit * $pageCurrent) + $limit < (int)$totalArticles) return true; else return false;

    }

    public function getPublishedTestimonials(array $filters, string $locale, ?int $page, $pageSize, $limit = null, array $options): array
    {
        $pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;

        $queryBuilder = $this->createQueryBuilder('testimonial')
            ->leftJoin('testimonial.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('translation.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($pageCurrent * $limit);

        $this->prepareFilter($queryBuilder, $filters);

        $testimonial = $queryBuilder->getQuery()->getResult();
        if (!$testimonial) {
            return [];
        }
        return $testimonial;
    }

    private function prepareFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['sortBy'])) {
            $queryBuilder->orderBy($filters['sortBy'], $filters['sortMethod']);
        }

        if (!empty($filters['tags']) || !empty($filters['categories'])) {
            $queryBuilder->leftJoin('testimonial.testimonialExcerpt', 'excerpt')
                ->leftJoin('excerpt.translations', 'excerpt_translation');
        }

        $this->prepareTagsFilter($queryBuilder, $filters);
        $this->prepareCategoriesFilter($queryBuilder, $filters);
    }

    private function prepareTagsFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (empty($filters['tags'])) {
            return;
        }

        $operator = $filters['tagOperator'] ?? 'or';

        if ($operator === 'and') {
            // AND: Entity must have ALL tags (multiple JOINs necessary)
            foreach ($filters['tags'] as $i => $tag) {
                $alias = 'tag' . $i;
                $queryBuilder
                    ->innerJoin('excerpt_translation.tags', $alias)
                    ->andWhere($queryBuilder->expr()->eq($alias . '.id', ':tag' . $i))
                    ->setParameter('tag' . $i, $tag);
            }
        } else {
            // OR: Entity must at least have one of the tags
            $queryBuilder
                ->leftJoin('excerpt_translation.tags', 'tags')
                ->andWhere($queryBuilder->expr()->in('tags.id', ':tags'))
                ->setParameter('tags', $filters['tags']);
        }
    }

    private function prepareCategoriesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (empty($filters['categories'])) {
            return;
        }

        $operator = $filters['categoryOperator'] ?? 'or';

        if ($operator === 'and') {
            // AND: Entity must have ALL categories (multiple JOINs necessary)
            $queryBuilder->leftJoin('excerpt_translation.categories', 'categories');

            foreach ($filters['categories'] as $i => $category) {
                $alias = 'category' . $i;
                $queryBuilder
                    ->innerJoin('excerpt_translation.categories', $alias)
                    ->andWhere($queryBuilder->expr()->eq($alias . '.id', ':category' . $i))
                    ->setParameter('category' . $i, $category);
            }
        } else {
            // OR: Entity must at least have one of the categories
            $queryBuilder
                ->leftJoin('excerpt_translation.categories', 'categories')
                ->andWhere($queryBuilder->expr()->in('categories.id', ':categories'))
                ->setParameter('categories', $filters['categories']);
        }
    }

    private function prepareTagsFilterX(QueryBuilder $queryBuilder, array $filters):void
    {
        if (!empty($filters['tags'])) {

            $queryBuilder->leftJoin('excerpt_translation.tags', 'tags');

            $i = 0;
            if ($filters['tagOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['tags'] as $tag) {
                    if ($i === 0) {
                        $andWhere .= "tags = :tag" . $i;
                    } else {
                        $andWhere .= " AND tags = :tag" . $i;
                    }
                    $queryBuilder->setParameter("tag" . $i, $tag);
                    $i++;
                }
                $queryBuilder->andWhere($andWhere);
            } else if ($filters['tagOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['tags'] as $tag) {
                    if ($i === 0) {
                        $orWhere .= "tags = :tag" . $i;
                    } else {
                        $orWhere .= " OR tags = :tag" . $i;
                    }
                    $queryBuilder->setParameter("tag" . $i, $tag);
                    $i++;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareCategoriesFilterX(QueryBuilder $queryBuilder, array $filters):void
    {
        if (!empty($filters['categories'])) {

            $queryBuilder->leftJoin('excerpt_translation.categories', 'categories');

            $i = 0;
            if ($filters['categoryOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $andWhere .= "categories = :category" . $i;
                    } else {
                        $andWhere .= " AND categories = :category" . $i;
                    }
                    $queryBuilder->setParameter("category" . $i, $category);
                }
                $queryBuilder->andWhere($andWhere);
            } else if ($filters['categoryOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $orWhere .= "categories = :category" . $i;
                    } else {
                        $orWhere .= " OR categories = :category" . $i;
                    }
                    $queryBuilder->setParameter("category" . $i, $category);
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

}
