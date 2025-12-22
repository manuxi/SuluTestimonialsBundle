<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\Type;

use Manuxi\SuluTestimonialsBundle\Content\Type\TestimonialsSelectionPropertyResolver;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialDimensionContentRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class TestimonialSelectionTest extends TestCase
{
    use ProphecyTrait;

    private TestimonialsSelectionPropertyResolver $resolver;
    private ObjectProphecy $repository;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(TestimonialDimensionContentRepository::class);
        $this->resolver = new TestimonialsSelectionPropertyResolver($this->repository->reveal());
    }

    public function testResolveEmpty(): void
    {
        $result = $this->resolver->resolve([], 'en');
        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertCount(0, $result->getContent());
        // view data is not strictly defined in resolve return value interface, 
        // but ContentView has getView().
        $view = $result->getView();
        $this->assertArrayHasKey('ids', $view);
        $this->assertEmpty($view['ids']);
    }

    public function testResolveValid(): void
    {
        $ids = [45, 22];
        $data = ['ids' => $ids];

        $entity1 = $this->prophesize(TestimonialDimensionContent::class);
        $entity2 = $this->prophesize(TestimonialDimensionContent::class);

        // Expect findBy usage
        $this->repository->findBy([
            'testimonial' => $ids,
            'locale' => 'en'
        ])->willReturn([$entity1->reveal(), $entity2->reveal()]);

        $result = $this->resolver->resolve($data, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertCount(2, $result->getContent());
        $this->assertSame($ids, $result->getView()['ids']);
    }
}
