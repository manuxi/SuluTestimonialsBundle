<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\ResourceLoader;

use Manuxi\SuluTestimonialsBundle\Content\ResourceLoader\TestimonialResourceLoader;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class TestimonialResourceLoaderTest extends TestCase
{
    use ProphecyTrait;

    public function testLoad(): void
    {
        $repository = $this->prophesize(TestimonialRepository::class);
        $loader = new TestimonialResourceLoader($repository->reveal());

        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getId()->willReturn(123);

        $repository->findByIds([123], 'en', DimensionContentInterface::STAGE_LIVE)->willReturn([$testimonial->reveal()]);

        $result = $loader->load(['123'], 'en');

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('123', $result);
        $this->assertSame($testimonial->reveal(), $result['123']);
    }

    public function testConstants(): void
    {
        $this->assertSame('testimonials', TestimonialResourceLoader::getKey());
        $this->assertSame(Testimonial::RESOURCE_KEY, TestimonialResourceLoader::getResourceKey());
    }
}
