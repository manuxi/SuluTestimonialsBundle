<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Interfaces;

interface AuthoredInterface
{
    public function getAuthored(): ?\DateTime;
    public function setAuthored(?\DateTime $authored);
}
