<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

trait ContactTrait
{

    #[ORM\ManyToOne(targetEntity: ContactInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?ContactInterface $contact = null;

    public function getContact(): ?ContactInterface
    {
        return $this->contact;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName("contact")]
    public function getContactData(): ?array
    {
        if ($contact = $this->getContact()) {
            return [
                'id' => $contact->getId(),
            ];
        }

        return null;

    }

    public function setContact(?ContactInterface $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

}
