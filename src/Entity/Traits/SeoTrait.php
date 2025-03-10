<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait SeoTrait
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hideInSitemap = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $noFollow = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $noIndex = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getHideInSitemap(): bool
    {
        return $this->hideInSitemap;
    }

    public function setHideInSitemap(bool $hideInSitemap): self
    {
        $this->hideInSitemap = $hideInSitemap;
        return $this;
    }

    public function getNoFollow(): bool
    {
        return $this->noFollow;
    }

    public function setNoFollow(bool $noFollow): self
    {
        $this->noFollow = $noFollow;
        return $this;
    }

    public function getNoIndex(): bool
    {
        return $this->noIndex;
    }

    public function setNoIndex(bool $noIndex): self
    {
        $this->noIndex = $noIndex;
        return $this;
    }

}
