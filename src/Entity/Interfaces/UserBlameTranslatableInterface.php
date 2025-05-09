<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Interfaces;

use Sulu\Component\Security\Authentication\UserInterface;

interface UserBlameTranslatableInterface
{
    /**
     * Returns the user id from the translation object which created it.
     */
    public function getCreator(): ?int;

    /**
     * Returns the user id from the translation object that changed it the last time.
     */
    public function getChanger(): ?int;
}
