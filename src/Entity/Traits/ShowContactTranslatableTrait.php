<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;

trait ShowContactTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "show_contact")]
    public function getShowContact(): ?bool
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getShowContact();
    }

    public function setShowContact(bool $showContact): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setShowContact($showContact);
        return $this;
    }
}
