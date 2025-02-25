<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\Type;

use Manuxi\SuluTestimonialsBundle\Content\Type\TestimonialsSelection;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Component\Content\Compat\PropertyInterface;

class TestimonialSelectionTest extends TestCase
{
    private TestimonialsSelection $testimonialSelection;
    private ObjectProphecy $testimonialRepository;

    protected function setUp(): void
    {
        $this->testimonialRepository = $this->prophesize(ObjectRepository::class);
        $entityManager         = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Testimonial::class)->willReturn($this->testimonialRepository->reveal());

        $this->testimonialSelection = new TestimonialsSelection($entityManager->reveal());
    }

    public function testNullValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(null);

        $this->assertSame([], $this->testimonialSelection->getContentData($property->reveal()));
        $this->assertSame(['ids' => null], $this->testimonialSelection->getViewData($property->reveal()));
    }

    public function testEmptyArrayValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn([]);

        $this->assertSame([], $this->testimonialSelection->getContentData($property->reveal()));
        $this->assertSame(['ids' => []], $this->testimonialSelection->getViewData($property->reveal()));
    }

    public function testValidValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn([45, 22]);

        $testimonial22 = $this->prophesize(Testimonial::class);
        $testimonial22->getId()->willReturn(22);

        $testimonial45 = $this->prophesize(Testimonial::class);
        $testimonial45->getId()->willReturn(45);

        $this->testimonialRepository->findBy(['id' => [45, 22]])->willReturn([
            $testimonial22->reveal(),
            $testimonial45->reveal(),
        ]);

        $this->assertSame(
            [
                $testimonial45->reveal(),
                $testimonial22->reveal(),
            ],
            $this->testimonialSelection->getContentData($property->reveal())
        );
        $this->assertSame(['ids' => [45, 22]], $this->testimonialSelection->getViewData($property->reveal()));
    }
}
