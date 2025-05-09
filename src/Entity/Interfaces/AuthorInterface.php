<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Interfaces;

use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

interface AuthorInterface
{
    public function getAuthor(): ?ContactInterface;
    public function setAuthor(?ContactInterface $author);
}
