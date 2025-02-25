<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\Type;

use Manuxi\SuluTestimonialsBundle\Content\Type\SingleTestimonialSelection;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Component\Content\Compat\PropertyInterface;

class SingleTestimonialSelectionTest extends TestCase
{
    private SingleTestimonialSelection $singleTestimonialSelection;

    private ObjectProphecy $testimonialRepository;

    protected function setUp(): void
    {
        $this->testimonialRepository = $this->prophesize(ObjectRepository::class);
        $entityManager         = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Testimonial::class)->willReturn($this->testimonialRepository->reveal());

        $this->singleTestimonialSelection = new SingleTestimonialSelection($entityManager->reveal());
    }

    public function testNullValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(null);

        $this->assertNull($this->singleTestimonialSelection->getContentData($property->reveal()));
        $this->assertSame(['id' => null], $this->singleTestimonialSelection->getViewData($property->reveal()));
    }

    public function testValidValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(45);

        $testimonial45 = $this->prophesize(Testimonial::class);

        $this->testimonialRepository->find(45)->willReturn($testimonial45->reveal());

        $this->assertSame($testimonial45->reveal(), $this->singleTestimonialSelection->getContentData($property->reveal()));
        $this->assertSame(['id' => 45], $this->singleTestimonialSelection->getViewData($property->reveal()));
    }
}
