<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\EventListener\Doctrine;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\AuthoredInterface;

class AuthoredListener
{
    const AUTHORED_PROPERTY_NAME = 'authored';

    /**
     * Load the class data, mapping the created and changed fields
     * to datetime fields.
     * @param LoadClassMetadataEventArgs $testimonial
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $testimonial)
    {
        $metadata = $testimonial->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (null !== $reflection && $reflection->implementsInterface('Manuxi\SuluTestimonialsBundle\Entity\Interfaces\AuthoredInterface')) {
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
     * @param LifecycleEventArgs $testimonial
     */
    public function preUpdate(LifecycleEventArgs $testimonial)
    {
        $this->handleTimestamp($testimonial);
    }

    /**
     * Set the timestamps before creation.
     * @param LifecycleEventArgs $testimonial
     */
    public function prePersist(LifecycleEventArgs $testimonial)
    {
        $this->handleTimestamp($testimonial);
    }

    /**
     * Set the timestamps. If created is NULL then set it. Always
     * set the changed field.
     * @param LifecycleEventArgs $testimonial
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
