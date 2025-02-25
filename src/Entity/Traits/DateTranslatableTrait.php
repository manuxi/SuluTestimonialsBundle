<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

trait DateTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "date")]
    public function getDate(): ?DateTime
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getDate();
    }

    public function setDate(?DateTime $date): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setDate($date);
        return $this;
    }
}
