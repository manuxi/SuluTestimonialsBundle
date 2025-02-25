<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

trait TimestampableTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "created")]
    public function getCreated(): ?DateTime
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getCreated();
    }

    public function setCreated(\DateTime $created): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setCreated($created);
        return $this;
    }

    #[Serializer\VirtualProperty(name: "changed")]
    public function getChanged(): ?DateTime
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getChanged();
    }

    public function setChanged(\DateTime $changed): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setChanged($changed);
        return $this;
    }
}
