<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

trait ContactTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "contact")]
    public function getContact(): ?int
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getContact() ? $translation->getContact()->getId() : null;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName("contact")]
    public function getContactData(): ?array
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getContactData();
    }

    public function setContact(?ContactInterface $contact): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setContact($contact);
        return $this;
    }
}
