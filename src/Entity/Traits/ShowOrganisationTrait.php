<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ShowOrganisationTrait
{

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showOrganisation = null;

    public function getShowOrganisation(): bool
    {
        return $this->showOrganisation ?? false;
    }

    public function setShowOrganisation(bool $showOrganisation): self
    {
        $this->showOrganisation = $showOrganisation;
        return $this;
    }

}
