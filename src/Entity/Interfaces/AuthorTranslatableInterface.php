<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Interfaces;

use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

interface AuthorTranslatableInterface
{
    public function getAuthor(): ?int;
    public function setAuthor(?ContactInterface $author);
}
