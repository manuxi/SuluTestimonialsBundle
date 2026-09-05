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

        $date = $object->getDate();
        if (null !== $date) {
            $normalizedData['date'] = $date->format('Y-m-d');
        }

        $contact = $object->getContact();
        if (null !== $contact) {
            $normalizedData['contact'] = [
                'id' => $contact->getId(),
                'firstName' => $contact->getFirstName(),
                'lastName' => $contact->getLastName(),
                'fullName' => trim($contact->getFirstName() . ' ' . $contact->getLastName()),
            ];
        }

        return $normalizedData;
    }
}
