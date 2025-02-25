<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialRatingSelect
{

    private array $typesMap = [
        '0' => 'sulu_testimonials.rates.0',
        '1' => 'sulu_testimonials.rates.1',
        '2' => 'sulu_testimonials.rates.2',
        '3' => 'sulu_testimonials.rates.3',
        '4' => 'sulu_testimonials.rates.4',
        '5' => 'sulu_testimonials.rates.5',
    ];
    private string $defaultValue = '3';

    public function __construct(private TranslatorInterface $translator)
    {}

    public function getValues(): array
    {
        $values = [];

        foreach ($this->typesMap as $key => $value) {
            $values[] = [
                'name' => $key,
                'title' => $this->translator->trans($value, [], 'admin'),
            ];
        }

        return $values;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }
}