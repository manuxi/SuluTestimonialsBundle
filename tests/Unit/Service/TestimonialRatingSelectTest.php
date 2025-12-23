<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Service;

use Manuxi\SuluTestimonialsBundle\Service\TestimonialRatingSelect;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialRatingSelectTest extends TestCase
{
    use ProphecyTrait;

    public function testValues(): void
    {
        $translator = $this->prophesize(TranslatorInterface::class);
        // Mock all calls with Argument::any() just to ensure it returns string to avoid TypeError
        $translator->trans(Argument::type('string'), [], 'admin')->willReturn('Translated');

        // Also provide specific returns if strictly testing values
        $translator->trans('sulu_testimonials.rates.0', [], 'admin')->willReturn('Bad');

        $ratingSelect = new TestimonialRatingSelect($translator->reveal());

        $values = $ratingSelect->getValues();

        $this->assertCount(6, $values);
        $this->assertEquals('0', $values[0]['name']);
        $this->assertEquals('Bad', $values[0]['title']);
        $this->assertEquals('Translated', $values[1]['title']); // Others will be 'Translated'
    }

    public function testDefaultValue(): void
    {
        $translator = $this->prophesize(TranslatorInterface::class);
        $ratingSelect = new TestimonialRatingSelect($translator->reveal());
        $this->assertEquals('3', $ratingSelect->getDefaultValue());
    }
}
