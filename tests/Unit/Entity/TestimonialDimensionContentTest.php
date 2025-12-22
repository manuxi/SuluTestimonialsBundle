<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Entity;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use PHPUnit\Framework\TestCase;

class TestimonialDimensionContentTest extends TestCase
{
    private TestimonialDimensionContent $dimensionContent;
    private Testimonial $testimonial;

    protected function setUp(): void
    {
        $this->testimonial = new Testimonial();
        $this->dimensionContent = new TestimonialDimensionContent($this->testimonial);
    }

    public function testTitle(): void
    {
        $this->assertNull($this->dimensionContent->getTitle());
        $this->assertSame($this->dimensionContent, $this->dimensionContent->setTitle('Test Title'));
        $this->assertSame('Test Title', $this->dimensionContent->getTitle());
    }

    public function testText(): void
    {
        $this->assertNull($this->dimensionContent->getText());
        $this->assertSame($this->dimensionContent, $this->dimensionContent->setText('Lorem ipsum'));
        $this->assertSame('Lorem ipsum', $this->dimensionContent->getText());
    }

    public function testRating(): void
    {
        $this->assertNull($this->dimensionContent->getRating());
        $this->assertSame($this->dimensionContent, $this->dimensionContent->setRating('5'));
        $this->assertSame('5', $this->dimensionContent->getRating());
    }

    public function testShowOrganisation(): void
    {
        $this->assertFalse($this->dimensionContent->getShowOrganisation());
        $this->assertSame($this->dimensionContent, $this->dimensionContent->setShowOrganisation(true));
        $this->assertTrue($this->dimensionContent->getShowOrganisation());
    }
}
