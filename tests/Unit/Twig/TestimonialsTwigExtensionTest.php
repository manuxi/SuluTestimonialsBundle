<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Twig;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Manuxi\SuluTestimonialsBundle\Twig\TestimonialsTwigExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class TestimonialsTwigExtensionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $testimonialRepository;
    private TestimonialsTwigExtension $extension;

    protected function setUp(): void
    {
        $this->testimonialRepository = $this->prophesize(TestimonialRepository::class);
        $this->extension = new TestimonialsTwigExtension(
            $this->testimonialRepository->reveal(),
            10
        );
    }

    public function testImplementsGlobalsInterface(): void
    {
        $this->assertInstanceOf(GlobalsInterface::class, $this->extension);
    }

    public function testGetGlobals(): void
    {
        $globals = $this->extension->getGlobals();

        $this->assertArrayHasKey('testimonials_rating_max_value', $globals);
        $this->assertEquals(10, $globals['testimonials_rating_max_value']);
    }

    public function testGetGlobalsWithDefaultValue(): void
    {
        $extensionWithDefault = new TestimonialsTwigExtension(
            $this->testimonialRepository->reveal()
        );

        $globals = $extensionWithDefault->getGlobals();

        $this->assertEquals(5, $globals['testimonials_rating_max_value']);
    }

    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(2, $functions);
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);
        $this->assertInstanceOf(TwigFunction::class, $functions[1]);
        $this->assertEquals('sulu_resolve_testimonial', $functions[0]->getName());
        $this->assertEquals('sulu_get_testimonials', $functions[1]->getName());
    }

    public function testResolveTestimonial(): void
    {
        $id = 1;
        $locale = 'en';
        $testimonial = $this->prophesize(Testimonial::class);

        $this->testimonialRepository->findById($id, $locale)->willReturn($testimonial->reveal());

        $result = $this->extension->resolveTestimonial($id, $locale);
        $this->assertSame($testimonial->reveal(), $result);
    }

    public function testResolveTestimonialNull(): void
    {
        $id = 999;
        $locale = 'en';
        $this->testimonialRepository->findById($id, $locale)->willReturn(null);

        $result = $this->extension->resolveTestimonial($id, $locale);
        $this->assertNull($result);
    }

    public function testGetTestimonials(): void
    {
        $limit = 5;
        $locale = 'de';
        $testimonials = [
            $this->prophesize(Testimonial::class)->reveal(),
            $this->prophesize(Testimonial::class)->reveal(),
        ];

        $this->testimonialRepository->findByFilters([], 0, $limit, $limit, $locale)->willReturn($testimonials);

        $result = $this->extension->getTestimonials($limit, $locale);
        $this->assertSame($testimonials, $result);
    }
}