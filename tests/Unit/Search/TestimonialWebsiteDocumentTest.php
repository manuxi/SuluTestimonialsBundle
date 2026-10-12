<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Search;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Search\TestimonialWebsiteDocument;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

class TestimonialWebsiteDocumentTest extends TestCase
{
    public function testDocumentCarriesThePreviewInMetadata(): void
    {
        $contact = $this->createMock(ContactInterface::class);
        $contact->method('getFirstName')->willReturn('Erika');
        $contact->method('getLastName')->willReturn('Muster');

        $content = $this->createMock(TestimonialDimensionContent::class);
        $content->method('getTitle')->willReturn('von Erika');
        $content->method('getText')->willReturn('<p>Sehr <strong>gut</strong> &amp; schnell.</p><p>Gerne wieder.</p>');
        $content->method('getSource')->willReturn('Google');
        $content->method('getRating')->willReturn('5');
        $content->method('getContact')->willReturn($contact);
        $content->method('getShowContact')->willReturn(true);
        $content->method('getShowDate')->willReturn(true);
        $content->method('getDate')->willReturn(new \DateTimeImmutable('2025-02-23 01:00:00', new \DateTimeZone('UTC')));

        $testimonial = $this->createMock(Testimonial::class);
        $testimonial->method('getId')->willReturn('abc');

        $document = TestimonialWebsiteDocument::create($testimonial, $content, 'de', ['site']);

        $this->assertSame('testimonial-abc-de', $document['id']);
        $this->assertSame(['site'], $document['webspaces']);
        $this->assertSame(['Sehr gut & schnell. Gerne wieder.', 'Google'], $document['content']);
        $this->assertSame([
            'description' => 'Sehr gut & schnell. Gerne wieder.',
            'author' => 'Erika Muster',
            'rating' => '5',
            'source' => 'Google',
            'date' => '2025-02-23T01:00:00+00:00',
        ], $document['metadata']['preview']);
        // only fields of the website schema are stored on the top level
        $this->assertArrayNotHasKey('contact', $document);
        $this->assertArrayNotHasKey('rating', $document);
    }

    public function testContactAndDateStayOutWhenTheTestimonialHidesThem(): void
    {
        $contact = $this->createMock(ContactInterface::class);
        $contact->method('getFirstName')->willReturn('Erika');
        $contact->method('getLastName')->willReturn('Muster');

        $content = $this->createMock(TestimonialDimensionContent::class);
        $content->method('getTitle')->willReturn('von Erika');
        $content->method('getContact')->willReturn($contact);
        $content->method('getShowContact')->willReturn(false);
        $content->method('getShowDate')->willReturn(false);
        $content->method('getDate')->willReturn(new \DateTimeImmutable('2025-02-23'));

        $testimonial = $this->createMock(Testimonial::class);
        $testimonial->method('getId')->willReturn('abc');

        $document = TestimonialWebsiteDocument::create($testimonial, $content, 'de', []);

        $this->assertSame([], $document['metadata']);
    }
}
