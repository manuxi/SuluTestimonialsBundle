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

        $translator->trans(Argument::any(), Argument::cetera())
            ->will(function ($args) {
                if ($args[0] === 'sulu_testimonials.rates.default.0') {
                    return 'Bad';
                }

                return 'Translated';
            });

        $ratingSelect = new TestimonialRatingSelect($translator->reveal());

        $values = $ratingSelect->getValues();

        $this->assertCount(6, $values);
        $this->assertEquals('0', $values[0]['name']);
        $this->assertEquals('Bad', $values[0]['title']);
        $this->assertEquals('Translated', $values[1]['title']);
    }

    public function testDefaultValue(): void
    {
        $translator = $this->prophesize(TranslatorInterface::class);
        $ratingSelect = new TestimonialRatingSelect($translator->reveal());
        $this->assertEquals('3', $ratingSelect->getDefaultValue());
    }
}
