<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\Type;

use Manuxi\SuluTestimonialsBundle\Content\Type\SingleTestimonialSelectionPropertyResolver;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialDimensionContentRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class SingleTestimonialSelectionTest extends TestCase
{
    use ProphecyTrait;

    private SingleTestimonialSelectionPropertyResolver $resolver;
    private ObjectProphecy $repository;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(TestimonialDimensionContentRepository::class);
        $this->resolver = new SingleTestimonialSelectionPropertyResolver($this->repository->reveal());
    }

    public function testResolveNull(): void
    {
        $result = $this->resolver->resolve(null, 'en');
        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertNull($result->getContent());
    }

    public function testResolveValid(): void
    {
        $entity = $this->prophesize(TestimonialDimensionContent::class);
        $this->repository->load('45', ['locale' => 'en'])->willReturn($entity->reveal());

        $result = $this->resolver->resolve(45, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertSame($entity->reveal(), $result->getContent());
        $this->assertSame(['id' => 45], $result->getView());
    }
}
