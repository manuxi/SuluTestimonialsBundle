<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\EventListener\Doctrine;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\AuthoredInterface;

class AuthoredListener
{
    public const AUTHORED_PROPERTY_NAME = 'authored';

    /**
     * Load the class data, mapping the created and changed fields
     * to datetime fields.
     *
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $testimonial)
    {
        $metadata = $testimonial->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (null !== $reflection && $reflection->implementsInterface('Manuxi\SuluSharedToolsBundle\Entity\Interfaces\AuthoredInterface')) {
            if (!$metadata->hasField(self::AUTHORED_PROPERTY_NAME)) {
                $metadata->mapField([
                    'fieldName' => self::AUTHORED_PROPERTY_NAME,
                    'type' => 'datetime',
                    'notnull' => true,
                ]);
            }
        }
    }

    /**
     * Set the timestamps before update.
     */
    public function preUpdate(LifecycleEventArgs $testimonial)
    {
        $this->handleTimestamp($testimonial);
    }

    /**
     * Set the timestamps before creation.
     */
    public function prePersist(LifecycleEventArgs $testimonial)
    {
        $this->handleTimestamp($testimonial);
    }

    /**
     * Set the timestamps. If created is NULL then set it. Always
     * set the changed field.
     */
    private function handleTimestamp(LifecycleEventArgs $testimonial)
    {
        $entity = $testimonial->getObject();

        if (!$entity instanceof AuthoredInterface) {
            return;
        }

        $meta = $testimonial->getObjectManager()->getClassMetadata(\get_class($entity));

        $authored = $meta->getFieldValue($entity, self::AUTHORED_PROPERTY_NAME);
        if (null === $authored) {
            $meta->setFieldValue($entity, self::AUTHORED_PROPERTY_NAME, new \DateTime());
        }
    }
}
