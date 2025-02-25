<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ShowContactTrait
{

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showContact = null;

    public function getShowContact(): bool
    {
        return $this->showContact ?? false;
    }

    public function setShowContact(bool $showContact): self
    {
        $this->showContact = $showContact;
        return $this;
    }

}
