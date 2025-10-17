<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\AuthorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthorListener
{
    const AUTHOR_PROPERTY_NAME = 'author';

    private TokenStorageInterface $tokenStorage;
    private string $userClass;

    public function __construct(string $userClass, ?TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userClass = $userClass;
    }

    /**
     * Map creator and changer fields to User objects.
     * @param LoadClassMetadataEventArgs $testimonial
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $testimonial)
    {
        $metadata = $testimonial->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (null !== $reflection && $reflection->implementsInterface('Manuxi\SuluSharedToolsBundle\Entity\Interfaces\AuthorInterface')) {
            if (!$metadata->hasAssociation(self::AUTHOR_PROPERTY_NAME)) {
                $metadata->mapManyToOne([
                    'fieldName' => self::AUTHOR_PROPERTY_NAME,
                    'targetEntity' => $this->userClass,
                    'joinColumns' => [
                        [
                            'name' => 'author_id',
                            'onDelete' => 'SET NULL',
                            'referencedColumnName' => 'id',
                            'nullable' => true,
                        ],
                    ],
                ]);
            }
        }
    }

    public function onFlush(OnFlushEventArgs $testimonial)
    {
        if (null === $this->tokenStorage) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        // if no token, do nothing
        if (null === $token || $token instanceof NullToken) {
            return;
        }

        $user = $this->getUser($token);

        // if no sulu user, do nothing
        if (!$user instanceof UserInterface) {
            return;
        }

        $contact = $user->getContact();

        $this->handleAuthor($testimonial, $contact, true);
        $this->handleAuthor($testimonial, $contact, false);
    }

    private function handleAuthor(OnFlushEventArgs $event, ContactInterface $contact, bool $insertions)
    {
        $manager = $event->getObjectManager();
        $unitOfWork = $manager->getUnitOfWork();

        $entities = $insertions ? $unitOfWork->getScheduledEntityInsertions() :
            $unitOfWork->getScheduledEntityUpdates();

        foreach ($entities as $authorEntity) {
            if (!$authorEntity instanceof AuthorInterface) {
                continue;
            }

            $meta = $manager->getClassMetadata(\get_class($authorEntity));

            $changeset = $unitOfWork->getEntityChangeSet($authorEntity);
            $recompute = false;

            if ($insertions
                && (!isset($changeset[self::AUTHOR_PROPERTY_NAME]) || null === $changeset[self::AUTHOR_PROPERTY_NAME][1])
            ) {
                $meta->setFieldValue($authorEntity, self::AUTHOR_PROPERTY_NAME, $contact);
                $recompute = true;
            }

            if (true === $recompute) {
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $authorEntity);
            }
        }
    }

    /**
     * Return the user from the token.
     *
     * @param TokenInterface $token
     * @return UserInterface|null
     */
    private function getUser(TokenInterface $token): ?UserInterface
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

}
