<?php
declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Sitemap;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Sitemap\TestimonialsSitemapProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\PortalInformation;

class TestimonialsSitemapProviderTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $entityManager;
    private ObjectProphecy $webspaceManager;
    private ObjectProphecy $repository;
    private TestimonialsSitemapProvider $provider;

    public function testBuild(): void
    {
        $this->mockPortalInformation();
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->repository->createQueryBuilder('testimonial')->willReturn($queryBuilder->reveal());
        $queryBuilder->leftJoin(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->leftJoin(Argument::any(), Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter(Argument::any(), Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->andWhere(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->select(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->orderBy(Argument::any(), Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->setFirstResult(0)->willReturn($queryBuilder->reveal());
        $queryBuilder->setMaxResults(TestimonialsSitemapProvider::PAGE_SIZE)->willReturn($queryBuilder->reveal());
        $query = $this->prophesize(Query::class);
        $queryBuilder->getQuery()->willReturn($query->reveal());
        $now = new DateTime();
        $query->getResult()->willReturn([
            [
                'id' => 1,
                'locale' => 'fr',
                'slug' => '/fr/test-1',
                'lastModified' => $now,
            ]
        ]);
        $altQueryBuilder = $this->prophesize(QueryBuilder::class);
        $this->repository->createQueryBuilder('testimonial')->will(function () use ($queryBuilder, $altQueryBuilder) {
            static $callCount = 0;
            $callCount++;
            return $callCount === 1 ? $queryBuilder->reveal() : $altQueryBuilder->reveal();
        });
        $altQueryBuilder->leftJoin(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($altQueryBuilder->reveal());
        $altQueryBuilder->leftJoin(Argument::any(), Argument::any())->willReturn($altQueryBuilder->reveal());
        $altQueryBuilder->setParameter(Argument::any(), Argument::any())->willReturn($altQueryBuilder->reveal());
        $altQueryBuilder->andWhere(Argument::any())->willReturn($altQueryBuilder->reveal());
        $altQueryBuilder->select(Argument::any())->willReturn($altQueryBuilder->reveal());
        $altQuery = $this->prophesize(Query::class);
        $altQueryBuilder->getQuery()->willReturn($altQuery->reveal());
        $altQuery->getResult()->willReturn([
            [
                'id' => 1,
                'locale' => 'de',
                'slug' => '/de/test-1',
            ],
            [
                'id' => 1,
                'locale' => 'en',
                'slug' => '/test-1',
            ]
        ]);
        $result = $this->provider->build(1, 'https', 'example.org');
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SitemapUrl::class, $result[0]);
        $this->assertEquals('https://example.org/fr/test-1', $result[0]->getLoc());
        $this->assertEquals('fr', $result[0]->getLocale());
    }

    private function mockPortalInformation(): void
    {
        $portalInfo = $this->prophesize(PortalInformation::class);
        $portalInfo->getLocale()->willReturn('en');
        $this->webspaceManager->findPortalInformationsByHostIncludingSubdomains('example.org', 'prod')
            ->willReturn([$portalInfo->reveal()]);
    }

    public function testGetMaxPage(): void
    {
        $this->mockPortalInformation();
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->repository->createQueryBuilder('testimonial')->willReturn($queryBuilder->reveal());
        $queryBuilder->select('COUNT(DISTINCT testimonial.uuid)')->willReturn($queryBuilder->reveal());
        $queryBuilder->leftJoin(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter(Argument::any(), Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->andWhere(Argument::any())->willReturn($queryBuilder->reveal());
        $query = $this->prophesize(Query::class);
        $queryBuilder->getQuery()->willReturn($query->reveal());
        $query->getSingleScalarResult()->willReturn(10); // 10 items, page size 10000 -> 1 page
        $maxPage = $this->provider->getMaxPage('https', 'example.org');
        $this->assertEquals(1, $maxPage);
    }

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->webspaceManager = $this->prophesize(WebspaceManagerInterface::class);
        $this->repository = $this->prophesize(EntityRepository::class);
        $this->entityManager->getRepository(Testimonial::class)->willReturn($this->repository->reveal());
        $this->provider = new TestimonialsSitemapProvider(
            $this->entityManager->reveal(),
            $this->webspaceManager->reveal()
        );
    }
}
