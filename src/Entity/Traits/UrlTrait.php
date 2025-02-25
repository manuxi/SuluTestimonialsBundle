<?php

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait UrlTrait
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $url = null;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }
}
