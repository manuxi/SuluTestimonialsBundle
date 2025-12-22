<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class TestimonialDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data,
    ): void {
        if (!$localizedDimensionContent instanceof TestimonialDimensionContent) {
            return;
        }

        if (!$unlocalizedDimensionContent instanceof TestimonialDimensionContent) {
            return;
        }

        $this->mapSimpleFields($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapImage($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapContact($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapAuthor($localizedDimensionContent, $data);
    }

    private function mapSimpleFields(
        TestimonialDimensionContent $unlocalizedContent,
        TestimonialDimensionContent $localizedContent,
        array $data
    ): void {
        if (array_key_exists('title', $data)) {
            $unlocalizedContent->setTitle($data['title']);
            $localizedContent->setTitle($data['title']);
        }
        if (array_key_exists('text', $data)) {
            $unlocalizedContent->setText($data['text']);
            $localizedContent->setText($data['text']);
        }
        if (array_key_exists('rating', $data)) {
            $unlocalizedContent->setRating($data['rating']);
            $localizedContent->setRating($data['rating']);
        }
        if (array_key_exists('source', $data)) {
            $unlocalizedContent->setSource($data['source']);
            $localizedContent->setSource($data['source']);
        }
        if (array_key_exists('website', $data)) {
            $unlocalizedContent->setWebsite($data['website']);
            $localizedContent->setWebsite($data['website']);
        }
        if (array_key_exists('showOrganisation', $data)) {
            $unlocalizedContent->setShowOrganisation((bool) $data['showOrganisation']);
            $localizedContent->setShowOrganisation((bool) $data['showOrganisation']);
        }
        if (array_key_exists('showContact', $data)) {
            $unlocalizedContent->setShowContact((bool) $data['showContact']);
            $localizedContent->setShowContact((bool) $data['showContact']);
        }
        if (array_key_exists('showDate', $data)) {
            $unlocalizedContent->setShowDate((bool) $data['showDate']);
            $localizedContent->setShowDate((bool) $data['showDate']);
        }
        if (array_key_exists('date', $data)) {
            $date = null;
            if ($data['date']) {
                try {
                    $date = new \DateTimeImmutable($data['date']);
                } catch (\Exception $e) {
                    // Ignore invalid date
                }
            }
            $unlocalizedContent->setDate($date);
            $localizedContent->setDate($date);
        }
    }

    private function mapImage(
        TestimonialDimensionContent $unlocalizedContent,
        TestimonialDimensionContent $localizedContent,
        array $data,
    ): void {
        if (!\array_key_exists('image', $data)) {
            return;
        }

        $imageId = $data['image'];

        if (\is_array($imageId) && isset($imageId['id'])) {
            $imageId = $imageId['id'];
        }

        $image = null;
        if ($imageId) {
            $image = $this->entityManager->getReference(
                Media::class,
                $imageId
            );
        }

        $unlocalizedContent->setImage($image);
        $localizedContent->setImage($image);
    }

    private function mapContact(
        TestimonialDimensionContent $unlocalizedContent,
        TestimonialDimensionContent $localizedContent,
        array $data
    ): void {
        if (!\array_key_exists('contact', $data)) {
            return;
        }

        $contactId = $data['contact'];

        if (\is_array($contactId) && isset($contactId['id'])) {
            $contactId = $contactId['id'];
        }

        if ($contactId) {
            $contact = $this->entityManager->getReference(ContactInterface::class, $contactId);
            $unlocalizedContent->setContact($contact);
            $localizedContent->setContact($contact);
        } else {
            $unlocalizedContent->setContact(null);
            $localizedContent->setContact(null);
        }
    }

    private function mapAuthor(TestimonialDimensionContent $localizedContent, array $data): void
    {
        if (\array_key_exists('author', $data)) {
            $authorId = $data['author'];
            if (\is_array($authorId) && isset($authorId['id'])) {
                $authorId = $authorId['id'];
            }
            $author = $authorId ? $this->entityManager->getReference(ContactInterface::class, $authorId) : null;
            $localizedContent->setAuthor($author);
        }

        if (\array_key_exists('authored', $data)) {
            $authored = $data['authored'] ? new \DateTimeImmutable($data['authored']) : new \DateTimeImmutable();
            $localizedContent->setAuthored($authored);
        }
    }
}
