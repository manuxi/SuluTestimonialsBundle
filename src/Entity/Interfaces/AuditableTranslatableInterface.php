<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Interfaces;

/**
 * Composite interface of TimestampableTranslatableInterface, AuthoredTranslatableInterface,
 * UserBlameTranslatableInterface and AuthorTranslatableInterface.
 */
interface AuditableTranslatableInterface extends TimestampableTranslatableInterface, AuthoredTranslatableInterface, UserBlameTranslatableInterface, AuthorTranslatableInterface
{
}
