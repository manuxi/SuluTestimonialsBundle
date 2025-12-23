<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Reference;

use Manuxi\SuluTestimonialsBundle\Reference\TestimonialReferenceStore;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\HttpCacheBundle\ReferenceStore\ReferenceStoreInterface;

class TestimonialReferenceStoreTest extends TestCase
{
    use ProphecyTrait;

    public function testGetAll(): void
    {
        $store = new TestimonialReferenceStore();

        $store->add('1', 'testimonials');
        $store->add('2', 'testimonials');

        $result = $store->getAll();

        $this->assertContains('testimonials-1', $result);
        $this->assertContains('testimonials-2', $result);
    }

    public function testGetReferenceStoreKey(): void
    {
        $store = new TestimonialReferenceStore();

        $this->assertInstanceOf(ReferenceStoreInterface::class, $store);
        $this->assertSame('testimonials', $store->getKey());
    }
}
