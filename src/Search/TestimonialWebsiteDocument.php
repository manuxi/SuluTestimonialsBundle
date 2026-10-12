<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Search;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;

/**
 * Builds the document of a testimonial for the "website" search index (the schema comes from Sulu, see
 * sulu/search config/schemas/website.php). Only the fields of that schema are stored; anything else is dropped by the
 * search adapter. Everything a result list needs beyond title, text and image therefore goes into "metadata.preview":
 *
 *   description (plain text, shortened), author (the contact, if the testimonial shows it), rating, source,
 *   date (ISO 8601, if the testimonial shows it)
 *
 * The provider (full reindex) and the listener (publish) both use this class, so a published testimonial looks the
 * same as a reindexed one.
 */
final class TestimonialWebsiteDocument
{
    private const DESCRIPTION_LENGTH = 500;

    /**
     * @param list<string> $webspaces
     *
     * @return array<string, mixed>
     */
    public static function create(Testimonial $testimonial, TestimonialDimensionContent $dimensionContent, string $locale, array $webspaces): array
    {
        $text = self::plain($dimensionContent->getText());
        $source = self::plain($dimensionContent->getSource());

        $content = array_values(array_filter([$text, $source], static fn (string $part): bool => '' !== $part));

        $contact = $dimensionContent->getContact();
        $author = null;
        // the flags are on by default: an unsaved flag shows the contact
        if ($contact && false !== $dimensionContent->getShowContact()) {
            $author = trim($contact->getFirstName() . ' ' . $contact->getLastName());
        }

        $date = false !== $dimensionContent->getShowDate() ? $dimensionContent->getDate() : null;

        $preview = array_filter([
            'description' => self::shorten($text),
            'author' => $author,
            'rating' => $dimensionContent->getRating(),
            'source' => $source,
            'date' => $date?->format('c'),
        ], static fn (mixed $value): bool => null !== $value && '' !== $value);

        return [
            'id' => 'testimonial-' . $testimonial->getId() . '-' . $locale,
            'resourceKey' => Testimonial::RESOURCE_KEY,
            'resourceId' => (string) $testimonial->getId(),
            'locale' => $locale,
            'webspaces' => $webspaces,
            'title' => $dimensionContent->getTitle() ?? '',
            'url' => $dimensionContent->getRoute()?->getSlug(),
            'content' => $content,
            'mediaId' => $dimensionContent->getImage()?->getId(),
            'metadata' => [] === $preview ? [] : ['preview' => $preview],
        ];
    }

    private static function plain(?string $html): string
    {
        if (null === $html) {
            return '';
        }

        // block ends become spaces, so that "<p>a</p><p>b</p>" does not read "ab"
        $text = (string) preg_replace('~</?(?:p|div|br|li|ul|ol|h[1-6]|blockquote|tr|td)\b[^>]*>~i', ' ', $html);
        $text = html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private static function shorten(string $text): string
    {
        if (mb_strlen($text) <= self::DESCRIPTION_LENGTH) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, self::DESCRIPTION_LENGTH), " ,;:.-") . '…';
    }
}
