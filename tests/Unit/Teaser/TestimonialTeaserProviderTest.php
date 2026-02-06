<?php
declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Teaser;

use Doctrine\Common\Collections\ArrayCollection;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Manuxi\SuluTestimonialsBundle\Teaser\TestimonialTeaserProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\AdminBundle\Teaser\Configuration\TeaserConfiguration;
use Sulu\Bundle\AdminBundle\Teaser\Teaser;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentEnhancer\ContentEnhancerInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialTeaserProviderTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $testimonialRepository;
    private ObjectProphecy $contentAggregator;
    private ObjectProphecy $contentEnhancer;
    private ObjectProphecy $translator;
    private TestimonialTeaserProvider $teaserProvider;

    public function testGetConfiguration(): void
    {
        $this->translator->trans('sulu_testimonials.testimonial', [], 'admin')
            ->willReturn('Testimonial');
        $this->translator->trans('sulu_testimonials.select_testimonial', [], 'admin')
            ->willReturn('Select testimonial');
        $configuration = $this->teaserProvider->getConfiguration();
        $this->assertInstanceOf(TeaserConfiguration::class, $configuration);
    }

    public function testFindWithEmptyIds(): void
    {
        $result = $this->teaserProvider->find([], 'en');
        $this->assertSame([], $result);
    }

    public function testFind(): void
    {
        $uuid = 'test-uuid-123';
        $locale = 'en';
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getUuid()->willReturn($uuid);
        $this->testimonialRepository->findByUuids(
            [$uuid],
            $locale,
            DimensionContentInterface::STAGE_LIVE
        )->willReturn([$testimonial->reveal()]);
        $dimensionContent = $this->prophesize(TestimonialDimensionContent::class);
        $dimensionContent->getExcerptTitle()->willReturn(null);
        $dimensionContent->getTitle()->willReturn('Test Testimonial');
        $dimensionContent->getText()->willReturn('This is a test testimonial text.');
        $dimensionContent->getExcerptDescription()->willReturn(null);
        $dimensionContent->getExcerptMore()->willReturn(null);
        $dimensionContent->getRoute()->willReturn(null);
        $dimensionContent->getImage()->willReturn(null);
        $dimensionContent->getExcerptImage()->willReturn(null);
        $this->contentAggregator->aggregate(
            $testimonial->reveal(),
            Argument::that(function ($arg) use ($locale) {
                return $arg['locale'] === $locale
                    && $arg['stage'] === DimensionContentInterface::STAGE_LIVE;
            })
        )->willReturn($dimensionContent->reveal());
        $this->contentEnhancer->enhance($dimensionContent->reveal())
            ->willReturn($dimensionContent->reveal());
        $teasers = $this->teaserProvider->find([$uuid], $locale);
        $this->assertCount(1, $teasers);
        $this->assertInstanceOf(Teaser::class, $teasers[0]);
        $this->assertSame($uuid, $teasers[0]->getId());
        $this->assertSame('Test Testimonial', $teasers[0]->getTitle());
        $this->assertSame('This is a test testimonial text.', $teasers[0]->getDescription());
    }

    public function testFindWithImage(): void
    {
        $uuid = 'test-uuid-456';
        $locale = 'de';
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getUuid()->willReturn($uuid);
        $this->testimonialRepository->findByUuids(
            [$uuid],
            $locale,
            DimensionContentInterface::STAGE_LIVE
        )->willReturn([$testimonial->reveal()]);
        $image = $this->prophesize(MediaInterface::class);
        $image->getId()->willReturn(42);
        $dimensionContent = $this->prophesize(TestimonialDimensionContent::class);
        $dimensionContent->getExcerptTitle()->willReturn('Excerpt Title');
        $dimensionContent->getTitle()->willReturn('Normal Title');
        $dimensionContent->getText()->willReturn(null);
        $dimensionContent->getExcerptDescription()->willReturn('Excerpt description.');
        $dimensionContent->getExcerptMore()->willReturn('Read more');
        $dimensionContent->getRoute()->willReturn(null);
        $dimensionContent->getImage()->willReturn($image->reveal());
        $dimensionContent->getExcerptImage()->willReturn(null);
        $this->contentAggregator->aggregate(
            $testimonial->reveal(),
            Argument::any()
        )->willReturn($dimensionContent->reveal());
        $this->contentEnhancer->enhance($dimensionContent->reveal())
            ->willReturn($dimensionContent->reveal());
        $teasers = $this->teaserProvider->find([$uuid], $locale);
        $this->assertCount(1, $teasers);
        $teaser = $teasers[0];
        // Excerpt title takes precedence
        $this->assertSame('Excerpt Title', $teaser->getTitle());
        $this->assertSame('Excerpt description.', $teaser->getDescription());
        $this->assertSame('Read more', $teaser->getMoreText());
        $this->assertSame(42, $teaser->getMediaId());
    }

    public function testFindWithNoTitle(): void
    {
        $uuid = 'test-uuid-789';
        $locale = 'en';
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getUuid()->willReturn($uuid);
        $this->testimonialRepository->findByUuids(
            [$uuid],
            $locale,
            DimensionContentInterface::STAGE_LIVE
        )->willReturn([$testimonial->reveal()]);
        $dimensionContent = $this->prophesize(TestimonialDimensionContent::class);
        $dimensionContent->getExcerptTitle()->willReturn(null);
        $dimensionContent->getTitle()->willReturn(null); // No title
        $this->contentAggregator->aggregate(
            $testimonial->reveal(),
            Argument::any()
        )->willReturn($dimensionContent->reveal());
        $this->contentEnhancer->enhance($dimensionContent->reveal())
            ->willReturn($dimensionContent->reveal());
        $teasers = $this->teaserProvider->find([$uuid], $locale);
        // Should return empty because no title
        $this->assertCount(0, $teasers);
    }

    public function testFindWithContentNotFoundException(): void
    {
        $uuid = 'test-uuid-not-found';
        $locale = 'en';
        $testimonial = $this->prophesize(Testimonial::class);
        $testimonial->getUuid()->willReturn($uuid);
        $testimonial->getId()->willReturn($uuid); // Required for ContentNotFoundException
        $testimonial->getDimensionContents()->willReturn(new ArrayCollection()); // Required for ContentNotFoundException
        $this->testimonialRepository->findByUuids(
            [$uuid],
            $locale,
            DimensionContentInterface::STAGE_LIVE
        )->willReturn([$testimonial->reveal()]);
        $this->contentAggregator->aggregate(
            $testimonial->reveal(),
            Argument::any()
        )->willThrow(new ContentNotFoundException($testimonial->reveal(), []));
        $teasers = $this->teaserProvider->find([$uuid], $locale);
        // Should return empty because ContentNotFoundException was thrown
        $this->assertCount(0, $teasers);
    }

    protected function setUp(): void
    {
        $this->testimonialRepository = $this->prophesize(TestimonialRepository::class);
        $this->contentAggregator = $this->prophesize(ContentAggregatorInterface::class);
        $this->contentEnhancer = $this->prophesize(ContentEnhancerInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->teaserProvider = new TestimonialTeaserProvider(
            $this->testimonialRepository->reveal(),
            $this->contentAggregator->reveal(),
            $this->contentEnhancer->reveal(),
            $this->translator->reveal()
        );
    }
}