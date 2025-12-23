<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Preview;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Preview\TestimonialsObjectProvider;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\PreviewBundle\Preview\PreviewContext;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class TestimonialsObjectProviderTest extends TestCase
{
    use ProphecyTrait;

    public function testGetDefaults(): void
    {
        $repository = $this->prophesize(TestimonialRepository::class);
        $contentAggregator = $this->prophesize(ContentAggregatorInterface::class);

        $provider = new TestimonialsObjectProvider(
            $repository->reveal(),
            $contentAggregator->reveal()
        );

        $context = new PreviewContext('123', 'en');

        $testimonial = $this->prophesize(Testimonial::class);
        $repository->findById(123)->willReturn($testimonial->reveal());

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentAggregator->aggregate($testimonial->reveal(), [
            'locale' => 'en',
            'stage' => DimensionContentInterface::STAGE_DRAFT
        ])->willReturn($dimensionContent->reveal());

        $result = $provider->getDefaults($context);

        $this->assertArrayHasKey('testimonial', $result);
        $this->assertArrayHasKey('dimensionContent', $result);
        $this->assertSame($testimonial->reveal(), $result['testimonial']);
        $this->assertSame($dimensionContent->reveal(), $result['dimensionContent']);
    }
}
