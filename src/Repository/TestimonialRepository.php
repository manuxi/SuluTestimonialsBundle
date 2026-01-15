<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Webmozart\Assert\Assert;

class TestimonialRepository extends ServiceEntityRepository
{
    public const GROUP_SELECT_TESTIMONIAL_ADMIN = 'testimonial_admin';
    public const GROUP_SELECT_TESTIMONIAL_WEBSITE = 'testimonial_website';

    public const SELECT_TESTIMONIAL_CONTENT = 'with-testimonial-content';

    private const SELECTS = [
        self::GROUP_SELECT_TESTIMONIAL_ADMIN => [
            self::SELECT_TESTIMONIAL_CONTENT => [
                DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_ADMIN => true,
            ],
        ],
        self::GROUP_SELECT_TESTIMONIAL_WEBSITE => [
            self::SELECT_TESTIMONIAL_CONTENT => [
                DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_WEBSITE => true,
            ],
        ],
    ];

    public function __construct(
        ManagerRegistry $registry,
        private DimensionContentQueryEnhancer $dimensionContentQueryEnhancer,
    ) {
        parent::__construct($registry, Testimonial::class);
    }

    public function findById(int $id): ?Testimonial
    {
        $qb = $this->createQueryBuilder('testimonial')
            ->leftJoin('testimonial.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent')
            ->where('testimonial.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByIds(array $ids, string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $filters = ['ids' => $ids, 'locale' => $locale, 'stage' => $stage];

        $qb = $this->buildQueryBuilder(
            $filters,
            [], // sort
            [self::GROUP_SELECT_TESTIMONIAL_WEBSITE => true]
        );

        return $qb->getQuery()->getResult();
    }

    public function findAllByLocale(string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $qb = $this->buildQueryBuilder(
            ['locale' => $locale, 'stage' => $stage],
            [], // sort
            [self::GROUP_SELECT_TESTIMONIAL_WEBSITE => true]
        );

        return $qb->getQuery()->getResult();
    }

    public function save(Testimonial $testimonial): void
    {
        $this->getEntityManager()->persist($testimonial);
        $this->getEntityManager()->flush();
    }

    public function add(Testimonial $testimonial): void
    {
        $this->getEntityManager()->persist($testimonial);
    }

    public function remove(Testimonial $testimonial): void
    {
        $this->getEntityManager()->remove($testimonial);
    }

    /**
     * @param array{
     *     locale?: string|null,
     *     stage?: string|null,
     *     limit?: int,
     *     offset?: int,
     *     id?: int,
     *     ids?: int[],
     *     categoryIds?: int[],
     *     tagIds?: int[],
     *     sortBy?: string,
     *     sortMethod?: 'asc'|'desc',
     * } $filters
     * @param int|null $page
     * @param int|null $pageSize
     * @param int|null $limit
     * @param string $locale
     * @param array $options
     */
    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $filters['locale'] = $locale;
        $filters['stage'] = $options['stage'] ?? DimensionContentInterface::STAGE_LIVE;
        $filters['limit'] = $limit;
        $filters['offset'] = ($page - 1) * $limit; // Check if page is 1-based usually

        // SmartContent passes filters as array.
        // We map SmartContent filters to buildQueryBuilder filters
        if (isset($filters['sortBy'])) {
            $sortBys = [$filters['sortBy'] => $filters['sortMethod'] ?? 'asc'];
        } else {
            $sortBys = [];
        }

        $selects = [self::GROUP_SELECT_TESTIMONIAL_WEBSITE => true];

        $qb = $this->buildQueryBuilder($filters, $sortBys, $selects);

        return $qb->getQuery()->getResult();
    }

    public function findAllForSitemap(string $locale, ?int $limit = null, ?int $offset = null): array
    {
        $filters = [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_LIVE,
            'limit' => $limit,
            'offset' => $offset,
            'published' => true,
        ];

        return $this->buildQueryBuilder($filters, ['created' => 'desc'])->getQuery()->getResult();
    }

    public function countForSitemap(string $locale): int
    {
        $filters = [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_LIVE,
            'published' => true,
        ];
        return $this->countBy($filters);
    }

    public function countBy(array $filters = []): int
    {
        $filters = $this->normalizeFindByFilters($filters);
        $selects = $this->normalizeSelects([]);
        $queryBuilder = $this->buildQueryBuilder($filters, [], $selects);

        $queryBuilder->select('COUNT(DISTINCT testimonial.id)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    private function buildQueryBuilder(
        array $filters = [],
        array $sortBys = [],
        array $selects = []
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('testimonial');

        $this->applyContentJoin($queryBuilder, $filters, $sortBys, $selects);
        $this->applyFilters($queryBuilder, $filters);
        $this->applySortBys($queryBuilder, $sortBys);
        $this->applyPagination($queryBuilder, $filters);

        return $queryBuilder;
    }

    private function normalizeFindByFilters(array $filters): array
    {
        $filters['stage'] = $filters['stage'] ?? DimensionContentInterface::STAGE_DRAFT;
        return $filters;
    }

    private function normalizeSelects(array $selects): array
    {
        $normalizedSelects = [];
        foreach (self::SELECTS as $groupKey => $groupSelects) {
            if (true === ($selects[$groupKey] ?? false)) {
                foreach ($groupSelects as $selectKey => $selectValue) {
                    $normalizedSelects[$selectKey] = $selectValue;
                }
            }
        }
        return $normalizedSelects;
    }

    private function applyContentJoin(
        QueryBuilder $queryBuilder,
        array $filters,
        array $sortBys,
        array $selects
    ): void {
        $locale = $filters['locale'] ?? null;
        $stage = $filters['stage'] ?? DimensionContentInterface::STAGE_DRAFT;

        $queryBuilder->leftJoin('testimonial.dimensionContents', 'dimensionContent');

        $normalizedSelects = $this->normalizeSelects($selects);
        if (!empty($normalizedSelects)) {
            $this->dimensionContentQueryEnhancer->addSelects(
                $queryBuilder,
                TestimonialDimensionContent::class,
                ['locale' => $locale, 'stage' => $stage],
                $normalizedSelects
            );
        } else {
            $queryBuilder->addSelect('dimensionContent');
        }
    }

    private function applyFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['id'])) {
            $queryBuilder->andWhere('testimonial.id = :id')
                ->setParameter('id', $filters['id']);
        }
        if (isset($filters['ids'])) {
            $queryBuilder->andWhere('testimonial.id IN (:ids)')
                ->setParameter('ids', $filters['ids']);
        }
        if (isset($filters['published']) && $filters['published']) {
            $queryBuilder->andWhere('dimensionContent.workflowPlace = :published')
                ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        }
        // Add more filters (categories, tags) if QueryEnhancer doesn't handle them fully via selects?
        // QueryEnhancer usually handles selects, but filters?
        // SuluEventBundle handles filters explicitly (e.g. date ranges).
        // For tags/categories, DimensionContentQueryEnhancer can help if we use correct join aliases?
        // Usually, filter 'tags' comes from SmartContent.
        // We should handle them if we want filtering by tags.
    }

    private function applySortBys(QueryBuilder $queryBuilder, array $sortBys): void
    {
        foreach ($sortBys as $field => $direction) {
            switch ($field) {
                case 'id':
                    $queryBuilder->addOrderBy('testimonial.id', $direction);
                    break;
                case 'title':
                case 'created':
                case 'changed':
                    $queryBuilder->addOrderBy('dimensionContent.' . $field, $direction);
                    break;
            }
        }
    }

    private function applyPagination(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['limit'])) {
            $queryBuilder->setMaxResults($filters['limit']);
        }
        if (isset($filters['offset'])) {
            $queryBuilder->setFirstResult($filters['offset']);
        }
    }
}
