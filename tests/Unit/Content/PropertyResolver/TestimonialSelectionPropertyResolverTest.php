<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\PropertyResolver;

use Manuxi\SuluTestimonialsBundle\Content\PropertyResolver\TestimonialSelectionPropertyResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class TestimonialSelectionPropertyResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $resolver = new TestimonialSelectionPropertyResolver();

        // Empty
        $result = $resolver->resolve([], 'en');
        $this->assertCount(0, $result->getContent());

        // Valid
        $result = $resolver->resolve([123, 456], 'en');
        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertSame(['ids' => [123, 456]], $result->getView());
    }

    public function testType(): void
    {
        $this->assertSame('testimonial_selection', TestimonialSelectionPropertyResolver::getType());
    }
}
