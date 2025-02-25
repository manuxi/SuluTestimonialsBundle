<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserBlameTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "creator")]
    public function getCreator(): ?int
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getCreator()->getId();
    }

    public function setCreator(?UserInterface $creator): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setCreator($creator);
        return $this;
    }

    #[Serializer\VirtualProperty(name: "changer")]
    public function getChanger(): ?int
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getChanger()->getId();
    }

    public function setChanger(?UserInterface $author): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setChanger($author);
        return $this;
    }
}
