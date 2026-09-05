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
    public function findByUuid(string $uuid): ?Testimonial
    {
        $qb = $this->createQueryBuilder('testimonial')
            ->leftJoin('testimonial.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent')
            ->where('testimonial.uuid = :uuid')
            ->setParameter('uuid', $uuid);
        return $qb->getQuery()->getOneOrNullResult();
    }
    public function findById(string $id): ?Testimonial
    {
        return $this->findByUuid($id);
    }
    public function findByUuids(array $uuids, string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $filters = ['uuids' => $uuids, 'locale' => $locale, 'stage' => $stage];
        $qb = $this->buildQueryBuilder(
            $filters,
            [], // sort
            [self::GROUP_SELECT_TESTIMONIAL_WEBSITE => true]
        );
        return $qb->getQuery()->getResult();
    }
    public function findAll(): array
    {
        $queryBuilder = $this->createQueryBuilder('testimonial')
            ->leftJoin('testimonial.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent');
        return $queryBuilder->getQuery()->getResult();
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
     *     id?: string,
     *     ids?: string[],
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
        $queryBuilder->select('COUNT(DISTINCT testimonial.uuid)');
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.uuid)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countPublished(string $locale): int
    {
        $qb = $this->createQueryBuilder('testimonial');
        $qb->select('COUNT(DISTINCT testimonial.uuid)')
            ->leftJoin('testimonial.dimensionContents', 'dc')
            ->where('dc.locale = :locale')
            ->andWhere('dc.stage = :stage')
            ->andWhere('dc.workflowPlace = :published')
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        return (int) $qb->getQuery()->getSingleScalarResult();
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

        if (isset($filters['uuid'])) {
            $queryBuilder->andWhere('testimonial.uuid = :uuid')
                ->setParameter('uuid', $filters['uuid']);
        }
        if (isset($filters['uuids'])) {
            $queryBuilder->andWhere('testimonial.uuid IN (:uuids)')
                ->setParameter('uuids', $filters['uuids']);
        }
        // Aliases for compatibility
        if (isset($filters['id']) && !isset($filters['uuid'])) {
            $queryBuilder->andWhere('testimonial.uuid = :id')
                ->setParameter('id', $filters['id']);
        }
        if (isset($filters['ids']) && !isset($filters['uuids'])) {
            $queryBuilder->andWhere('testimonial.uuid IN (:ids)')
                ->setParameter('ids', $filters['ids']);
        }
        if (isset($filters['published']) && $filters['published']) {
            $queryBuilder->andWhere('dimensionContent.workflowPlace = :published')
                ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        }
    }
    private function applySortBys(QueryBuilder $queryBuilder, array $sortBys): void
    {
        foreach ($sortBys as $field => $direction) {
            switch ($field) {
                case 'id':
                    $queryBuilder->addOrderBy('testimonial.uuid', $direction);
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