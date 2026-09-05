<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\Normalizer;

use Manuxi\SuluTestimonialsBundle\Content\Normalizer\TestimonialNormalizer;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\PropertyResolver\Resolver\DatePropertyResolver;

class TestimonialDateResolverTest extends TestCase
{
    public function testDateSurvivesSuluDatePropertyResolution(): void
    {
        $content = new TestimonialDimensionContent(new Testimonial());
        $content->setDate(new \DateTimeImmutable('2022-10-24T00:00:00+00:00'));

        $normalized = (new TestimonialNormalizer())->enhance($content, []);
        $resolved = (new DatePropertyResolver())->resolve($normalized['date'], 'de');

        self::assertSame('2022-10-24', $resolved->getContent());
    }
}
