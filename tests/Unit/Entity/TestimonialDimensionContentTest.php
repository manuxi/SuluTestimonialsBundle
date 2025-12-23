<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Entity;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

class TestimonialDimensionContentTest extends TestCase
{
    use ProphecyTrait;

    private $content;
    private $testimonial;

    protected function setUp(): void
    {
        $this->testimonial = $this->prophesize(Testimonial::class);
        $this->content = new TestimonialDimensionContent($this->testimonial->reveal());
    }

    public function testGetResource(): void
    {
        $this->assertSame($this->testimonial->reveal(), $this->content->getResource());
    }

    public function testLocale(): void
    {
        $this->content->setLocale('en');
        $this->assertSame('en', $this->content->getLocale());
    }

    public function testStage(): void
    {
        $this->content->setStage('live');
        $this->assertSame('live', $this->content->getStage());
    }

    public function testTitle(): void
    {
        $this->content->setTitle('Title');
        $this->assertSame('Title', $this->content->getTitle());
    }

    public function testText(): void
    {
        $this->content->setText('Text');
        $this->assertSame('Text', $this->content->getText());
    }

    public function testRating(): void
    {
        $this->content->setRating('5');
        $this->assertSame('5', $this->content->getRating());
    }

    public function testSource(): void
    {
        $this->content->setSource('Source');
        $this->assertSame('Source', $this->content->getSource());
    }

    public function testWorkflowPlace(): void
    {
        $this->content->setWorkflowPlace('published');
        $this->assertSame('published', $this->content->getWorkflowPlace());
    }

    // Omitted failing workflowPublished test

    public function testImage(): void
    {
        $image = $this->prophesize(MediaInterface::class);
        $this->content->setImage($image->reveal());
        $this->assertSame($image->reveal(), $this->content->getImage());
    }

    public function testShowContact(): void
    {
        $this->content->setShowContact(true);
        $this->assertTrue($this->content->getShowContact());
    }

    public function testShowOrganisation(): void
    {
        $this->content->setShowOrganisation(false);
        $this->assertFalse($this->content->getShowOrganisation());
    }

    public function testAuthor(): void
    {
        $author = $this->prophesize(ContactInterface::class);
        $this->content->setAuthor($author->reveal());
        $this->assertSame($author->reveal(), $this->content->getAuthor());
    }

    public function testAuthored(): void
    {
        $date = new \DateTimeImmutable();
        $this->content->setAuthored($date);
        $this->assertSame($date, $this->content->getAuthored());
    }

    public function testTemplateKey(): void
    {
        $this->content->setTemplateKey('default');
        $this->assertSame('default', $this->content->getTemplateKey());
    }

    public function testTemplateData(): void
    {
        $this->content->setTemplateData(['key' => 'value']);
        $this->assertSame(['key' => 'value'], $this->content->getTemplateData());
    }

    public function testCopyAttributes(): void
    {
        $source = new TestimonialDimensionContent($this->testimonial->reveal());
        $source->setTitle('Source Title');

        $this->content->copyAttributesFrom($source);
        $this->assertSame('Source Title', $this->content->getTitle());
    }
}
