<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialRatingSelect
{
    private int $maxValue;
    private int $defaultValue;
    private bool $useStarSymbols;

    public function __construct(
        private TranslatorInterface $translator,
        int $maxValue = 5,
        int $defaultValue = 3,
        bool $useStarSymbols = false
    ) {
        $this->maxValue = $maxValue;
        $this->defaultValue = min($defaultValue, $maxValue);
        $this->useStarSymbols = $useStarSymbols;
    }

    public function getValues(): array
    {
        $values = [];

        for ($i = 0; $i <= $this->maxValue; $i++) {
            $values[] = [
                'name' => (string) $i,
                'title' => $this->getTranslatedRating($i),
            ];
        }

        return $values;
    }

    public function getDefaultValue(): string
    {
        return (string) $this->defaultValue;
    }

    public function getMaxValue(): int
    {
        return $this->maxValue;
    }

    public function useStarSymbols(): bool
    {
        return $this->useStarSymbols;
    }

    private function getTranslatedRating(int $rating): string
    {
        $translationKey = $this->getTranslationKey($rating);
        $translated = $this->translator->trans($translationKey, [], 'admin');

        // If no translation exists, use fallback
        if ($translated === $translationKey) {
            return $this->getFallbackRepresentation($rating);
        }

        return $translated;
    }

    private function getTranslationKey(int $rating): string
    {
        if ($this->useStarSymbols) {
            $format = $this->maxValue === 10 ? 'ten' : 'five';
            return 'sulu_testimonials.rates.' . $format . '.' . $rating;
        }

        return 'sulu_testimonials.rates.default.' . $rating;
    }

    private function getFallbackRepresentation(int $rating): string
    {
        if ($this->useStarSymbols) {
            return $this->getStarRepresentation($rating);
        }

        // Text fallback
        if ($rating === 0) {
            return 'No rating';
        }
        if ($rating === 1) {
            return '1 star';
        }
        return $rating . ' stars';
    }

    private function getStarRepresentation(int $rating): string
    {
        if ($this->maxValue === 10) {
            // 10-point scale: 5 stars with half-star increments
            $fullStars = (int) floor($rating / 2);
            $halfStar = ($rating % 2 === 1);
            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

            $display = str_repeat('★', $fullStars);
            if ($halfStar) {
                $display .= '⯪';
            }
            $display .= str_repeat('☆', $emptyStars);

            return $display . ' (' . $rating . '/10)';
        }

        // 5-point scale: simple full stars
        $filledStars = str_repeat('★', $rating);
        $emptyStars = str_repeat('☆', $this->maxValue - $rating);

        return $filledStars . $emptyStars . ' (' . $rating . '/' . $this->maxValue . ')';
    }
}