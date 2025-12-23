<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\PropertyResolver;

use Manuxi\SuluTestimonialsBundle\Content\PropertyResolver\SingleTestimonialSelectionPropertyResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class SingleTestimonialSelectionPropertyResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $resolver = new SingleTestimonialSelectionPropertyResolver();

        $result = $resolver->resolve(null, 'en');
        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertNull($result->getContent());

        $result = $resolver->resolve(123, 'en', ['properties' => ['foo']]);
        $this->assertInstanceOf(ContentView::class, $result);

        $this->assertSame(['id' => 123, 'properties' => ['foo']], $result->getView());
    }

    public function testType(): void
    {
        $this->assertSame('single_testimonial_selection', SingleTestimonialSelectionPropertyResolver::getType());
    }
}
