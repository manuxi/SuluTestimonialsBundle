<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\Normalizer;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Sulu\Content\Application\ContentNormalizer\Normalizer\NormalizerInterface;

class TestimonialNormalizer implements NormalizerInterface
{
    public function getIgnoredAttributes(object $object): array
    {
        if (!$object instanceof TestimonialDimensionContent) {
            return [];
        }

        return [
            'testimonial',
            'image',
            'contact',
        ];
    }

    public function enhance(object $object, array $normalizedData): array
    {
        if (!$object instanceof TestimonialDimensionContent) {
            return $normalizedData;
        }

        $image = $object->getImage();
        if (null !== $image) {
            if (!isset($normalizedData['image']) || !\is_array($normalizedData['image'])) {
                $normalizedData['image'] = [];
            }
            $normalizedData['image']['id'] = $image->getId();
        }

        $contact = $object->getContact();
        if (null !== $contact) {
            if (!isset($normalizedData['contact']) || !\is_array($normalizedData['contact'])) {
                $normalizedData['contact'] = [];
            }
            $normalizedData['contact']['id'] = $contact->getId();
        }

        return $normalizedData;
    }
}
