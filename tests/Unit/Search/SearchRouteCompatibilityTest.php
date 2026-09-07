<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Search;

use CmsIg\Seal\EngineInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Search\TestimonialSearchListener;
use Manuxi\SuluTestimonialsBundle\Search\TestimonialWebsiteSearchProvider;
use PHPUnit\Framework\TestCase;
use Sulu\Route\Domain\Model\Route;

final class SearchRouteCompatibilityTest extends TestCase
{
    public function testWebsiteReindexUsesSulu3RouteSlug(): void
    {
        $testimonial = new Testimonial();
        $dimension = new TestimonialDimensionContent($testimonial);
        $dimension->setRoute(new Route('testimonials', $testimonial->getId(), 'de', '/kundenstimmen/test'));
        $provider = (new \ReflectionClass(TestimonialWebsiteSearchProvider::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($provider, 'createDocument');
        $document = $method->invoke($provider, $testimonial, $dimension, 'de');
        self::assertSame('/kundenstimmen/test', $document['url']);
    }

    public function testWebsiteListenerUsesSulu3RouteSlug(): void
    {
        $testimonial = new Testimonial();
        $dimension = new TestimonialDimensionContent($testimonial);
        $dimension->setRoute(new Route('testimonials', $testimonial->getId(), 'de', '/kundenstimmen/test'));
        $engine = $this->createMock(EngineInterface::class);
        $engine->expects(self::once())->method('saveDocument')->with('website', self::callback(static fn(array $document): bool => $document['url'] === '/kundenstimmen/test'));
        $listener = (new \ReflectionClass(TestimonialSearchListener::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($listener, 'engine'))->setValue($listener, $engine);
        (new \ReflectionMethod($listener, 'indexForWebsite'))->invoke($listener, $testimonial, $dimension, 'de');
    }
}
