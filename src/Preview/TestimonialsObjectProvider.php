<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Preview;

use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\PageBundle\Admin\PageAdmin;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class TestimonialsObjectProvider implements PreviewObjectProviderInterface
{
    private TestimonialRepository $repository;

    public function __construct(TestimonialRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getObject($id, $locale)
    {
        return $this->repository->findById((int)$id, $locale);
    }

    public function getId($object)
    {
        return $object->getId();
    }

    public function setValues($object, $locale, array $data)
    {
        // TODO: Implement setValues() method.
    }

    public function setContext($object, $locale, array $context)
    {
        if (\array_key_exists('template', $context)) {
            $object->setStructureType($context['template']);
        }

        return $object;
    }

    public function serialize($object)
    {
        return serialize($object);
    }

    public function deserialize($serializedObject, $objectClass)
    {
        return unserialize($serializedObject);
    }
    
    public function getSecurityContext($id, $locale): ?string
    {
        $webspaceKey = $this->documentInspector->getWebspace($this->getObject($id, $locale));

        return PageAdmin::getPageSecurityContext($webspaceKey);
    }
}
