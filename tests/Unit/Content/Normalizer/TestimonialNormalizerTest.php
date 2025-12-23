<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\Normalizer;

use Manuxi\SuluTestimonialsBundle\Content\Normalizer\TestimonialNormalizer;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

class TestimonialNormalizerTest extends TestCase
{
    use ProphecyTrait;

    private TestimonialNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new TestimonialNormalizer();
    }

    public function testGetIgnoredAttributes(): void
    {
        $object = $this->prophesize(TestimonialDimensionContent::class);
        $result = $this->normalizer->getIgnoredAttributes($object->reveal());

        $this->assertEquals(['testimonial', 'image', 'contact'], $result);

        $otherObject = new \stdClass();
        $this->assertEquals([], $this->normalizer->getIgnoredAttributes($otherObject));
    }

    public function testEnhance(): void
    {
        $object = $this->prophesize(TestimonialDimensionContent::class);
        $image = $this->prophesize(MediaInterface::class);
        $image->getId()->willReturn(123);
        $contact = $this->prophesize(ContactInterface::class);
        $contact->getId()->willReturn(456);

        $object->getImage()->willReturn($image->reveal());
        $object->getContact()->willReturn($contact->reveal());

        $normalizedData = ['foo' => 'bar'];

        $result = $this->normalizer->enhance($object->reveal(), $normalizedData);

        $this->assertArrayHasKey('image', $result);
        $this->assertEquals(123, $result['image']['id']);
        $this->assertArrayHasKey('contact', $result);
        $this->assertEquals(456, $result['contact']['id']);
        $this->assertEquals('bar', $result['foo']);
    }

    public function testEnhanceNoImageContact(): void
    {
        $object = $this->prophesize(TestimonialDimensionContent::class);
        $object->getImage()->willReturn(null);
        $object->getContact()->willReturn(null);

        $normalizedData = ['foo' => 'bar'];

        $result = $this->normalizer->enhance($object->reveal(), $normalizedData);

        $this->assertArrayNotHasKey('image', $result);
        $this->assertArrayNotHasKey('contact', $result);
        $this->assertEquals('bar', $result['foo']);
    }

    public function testEnhanceOtherObject(): void
    {
        $object = new \stdClass();
        $normalizedData = ['foo' => 'bar'];
        $result = $this->normalizer->enhance($object, $normalizedData);
        $this->assertEquals($normalizedData, $result);
    }
}
