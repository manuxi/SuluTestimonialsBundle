<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluTestimonialsBundle\Admin\TestimonialsAdmin;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\Builder;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\BuilderInterface;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Bundle\AdminBundle\SmartContent\SmartContentProviderInterface;
use Sulu\Bundle\AdminBundle\SmartContent\SmartContentQueryEnhancer;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialSmartContentProvider implements SmartContentProviderInterface
{
    private string $dimensionContentClassName;
    private ?TestimonialRepository $testimonialRepository = null;

    public function __construct(
        private DimensionContentQueryEnhancer $dimensionContentQueryEnhancer,
        private SmartContentQueryEnhancer $smartContentQueryEnhancer,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
        $this->dimensionContentClassName = TestimonialDimensionContent::class;
    }

    private function getTestimonialRepository(): TestimonialRepository
    {
        if (null === $this->testimonialRepository) {
            $repository = $this->entityManager->getRepository(Testimonial::class);
            if (!$repository instanceof TestimonialRepository) {
                throw new \RuntimeException(sprintf('Expected TestimonialRepository, got %s', get_class($repository)));
            }
            $this->testimonialRepository = $repository;
        }
        return $this->testimonialRepository;
    }

    public function getConfiguration(): ProviderConfigurationInterface
    {
        return $this->getConfigurationBuilder()->getConfiguration();
    }

    protected function getConfigurationBuilder(): BuilderInterface
    {
        return Builder::create()
            ->enableLimit()
            ->enablePagination()
            ->enablePresentAs()
            // Testimonials usually don't have categories/tags in strict sense unless excerpt/traits enabled.
            // DimensionContent has ExcerptTrait, so we can enable tags/categories if we want.
            // ->enableTags() 
            // ->enableCategories()
            ->enableSorting($this->getSorting())
            ->enableView(TestimonialsAdmin::EDIT_FORM_VIEW, ['id' => 'id']);
    }

    protected function getSorting(): array
    {
        return [
            ['column' => 'title', 'title' => $this->translator->trans('sulu_testimonials.title', [], 'admin')],
            ['column' => 'rating', 'title' => $this->translator->trans('sulu_testimonials.rating', [], 'admin')],
            ['column' => 'created', 'title' => $this->translator->trans('sulu_testimonials.created', [], 'admin')],
        ];
    }

    public function countBy(array $filters, array $params = []): int
    {
        $filters = $this->enhanceWithDimensionAttributes($filters);
        $alias = 'testimonial';
        $queryBuilder = $this->getTestimonialRepository()->createQueryBuilder($alias);

        $filters = $this->mapFilters($filters);

        $this->dimensionContentQueryEnhancer->addFilters(
            $queryBuilder,
            $alias,
            $this->dimensionContentClassName,
            $filters,
            []
        );
        $this->addInternalFilters($queryBuilder, $filters, $alias);

        $queryBuilder->select('COUNT(DISTINCT ' . $alias . '.id)');
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findFlatBy(array $filters, array $sortBys, array $params = []): array
    {
        $filters = $this->enhanceWithDimensionAttributes($filters);
        $alias = 'testimonial';
        $queryBuilder = $this->getTestimonialRepository()->createQueryBuilder($alias);

        $filters = $this->mapFilters($filters);
        $this->dimensionContentQueryEnhancer->addFilters(
            $queryBuilder,
            $alias,
            $this->dimensionContentClassName,
            $filters,
            $sortBys
        );
        $dimensionContentAlias = $this->addInternalFilters($queryBuilder, $filters, $alias);

        $queryBuilder->select('DISTINCT ' . $alias . '.id as id');
        $queryBuilder->addSelect($dimensionContentAlias . '.title');
        $queryBuilder->addSelect($dimensionContentAlias . '.rating');
        $queryBuilder->addSelect($dimensionContentAlias . '.workflowPlace');
        $queryBuilder->addSelect($dimensionContentAlias . '.workflowPublished');

        $this->smartContentQueryEnhancer->addOrderBySelects($queryBuilder);

        $limit = isset($filters['limit']) ? (int) $filters['limit'] : null;
        $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;
        $this->smartContentQueryEnhancer->addPagination($queryBuilder, $offset, $limit);

        $queryResult = $queryBuilder->getQuery()->getArrayResult();

        return array_map(function ($item) {
            return [
                'id' => (string) $item['id'],
                'title' => (string) ($item['title'] ?? ''),
                'rating' => (string) ($item['rating'] ?? ''),
                'publishedState' => 'published' === ($item['workflowPlace'] ?? ''),
                'published' => $item['workflowPublished'] ?? null,
            ];
        }, $queryResult);
    }

    protected function enhanceWithDimensionAttributes(array $filters): array
    {
        return array_merge([
            'stage' => $filters['stage'] ?? DimensionContentInterface::STAGE_LIVE,
        ], $filters);
    }

    protected function mapFilters(array $filters): array
    {
        // Map SmartContent filters to QueryEnhancer expected filters
        $mapped = [
            'locale' => $filters['locale'],
            'stage' => $filters['stage'] ?? null,
            'limit' => $filters['limit'] ?? null,
            'dataSource' => $filters['dataSource'] ?? null,
        ];
        if (isset($filters['offset']))
            $mapped['offset'] = $filters['offset'];
        return $mapped;
    }

    protected function addInternalFilters(QueryBuilder $queryBuilder, array $filters, string $alias): string
    {
        $dimensionContentAlias = null;
        $joins = $queryBuilder->getDQLPart('join');

        if (isset($joins[$alias])) {
            foreach ($joins[$alias] as $join) {
                if ($join->getJoin() === $alias . '.dimensionContents') {
                    $dimensionContentAlias = $join->getAlias();
                    break;
                }
            }
        }

        if (!$dimensionContentAlias) {
            $dimensionContentAlias = 'dimensionContent';
            $stage = $filters['stage'] ?? DimensionContentInterface::STAGE_LIVE;
            $locale = $filters['locale'];

            $queryBuilder->innerJoin(
                $alias . '.dimensionContents',
                $dimensionContentAlias,
                'WITH',
                $dimensionContentAlias . '.locale = :locale AND ' . $dimensionContentAlias . '.stage = :stage'
            );
            $queryBuilder->setParameter('locale', $locale);
            $queryBuilder->setParameter('stage', $stage);
        }

        return $dimensionContentAlias;
    }

    public function getType(): string
    {
        return Testimonial::RESOURCE_KEY;
    }

    public function getResourceLoaderKey(): string
    {
        return Testimonial::RESOURCE_KEY;
    }
}
