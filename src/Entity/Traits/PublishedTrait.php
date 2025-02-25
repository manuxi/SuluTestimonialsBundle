<?php

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait PublishedTrait
{

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $published = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $publishedAt = null;

    public function isPublished(): ?bool
    {
        return $this->published ?? false;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;
        if($published === true){
            $this->setPublishedAt(new DateTime());
        } else {
            $this->setPublishedAt(null);
        }
        return $this;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }
}
