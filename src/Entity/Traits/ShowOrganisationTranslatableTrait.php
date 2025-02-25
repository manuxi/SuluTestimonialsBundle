<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;

trait ShowOrganisationTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "show_contact")]
    public function getShowOrganisation(): ?bool
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getShowOrganisation();
    }

    public function setShowOrganisation(bool $showOrganisation): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setShowOrganisation($showOrganisation);
        return $this;
    }
}
