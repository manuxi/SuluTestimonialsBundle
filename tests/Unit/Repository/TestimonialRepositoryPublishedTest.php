<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;

/**
 * Sulu 3 leaves workflowPlace NULL on the live dimension when publishing, so "published" must mean
 * "a live row exists", not "workflowPlace is published".
 */
class TestimonialRepositoryPublishedTest extends TestCase
{
    private ?string $dql = null;
    private TestimonialRepository $repository;

    protected function setUp(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('getSingleScalarResult')->willReturn('3');
        $query->method('setParameters')->willReturnSelf();
        $query->method('setFirstResult')->willReturnSelf();
        $query->method('setMaxResults')->willReturnSelf();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->willReturn(new ClassMetadata(Testimonial::class));
        $entityManager->method('createQueryBuilder')->willReturnCallback(fn () => new QueryBuilder($entityManager));
        $entityManager->method('createQuery')->willReturnCallback(function (string $dql) use ($query) {
            $this->dql = $dql;

            return $query;
        });

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($entityManager);

        $this->repository = new TestimonialRepository($registry, new DimensionContentQueryEnhancer());
    }

    public function testCountPublishedCountsLiveRowsWithoutLookingAtWorkflowPlace(): void
    {
        $this->assertSame(3, $this->repository->countPublished('de'));

        $this->assertStringContainsString('dc.stage = :stage', (string) $this->dql);
        $this->assertStringNotContainsString('workflowPlace', (string) $this->dql);
    }

    public function testPublishedFilterMeansLiveRowAndNotWorkflowPlace(): void
    {
        $queryBuilder = new QueryBuilder($this->createMock(EntityManagerInterface::class));
        $queryBuilder->select('testimonial')->from(Testimonial::class, 'testimonial');

        $method = new \ReflectionMethod($this->repository, 'applyFilters');
        $method->invoke($this->repository, $queryBuilder, ['published' => true, 'locale' => 'de']);

        $dql = $queryBuilder->getDQL();
        $this->assertStringContainsString('dimensionContent.stage = :publishedStage', $dql);
        $this->assertStringContainsString('dimensionContent.locale = :publishedLocale', $dql);
        $this->assertStringNotContainsString('workflowPlace', $dql);
    }
}
