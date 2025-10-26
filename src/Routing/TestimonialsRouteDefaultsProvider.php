<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Routing;

use Manuxi\SuluTestimonialsBundle\Controller\Website\TestimonialsController;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;

class TestimonialsRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    private TestimonialRepository $repository;

    public function __construct(TestimonialRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param null $object
     *
     * @return mixed[]
     */
    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        return [
            '_controller' => TestimonialsController::class.'::indexAction',
            'testimonial' => $this->repository->findById((int) $id, $locale),
        ];
    }

    public function isPublished($entityClass, $id, $locale): bool
    {
        $testimonial = $this->repository->findById((int) $id, $locale);
        if (!$this->supports($entityClass) || !$testimonial instanceof Testimonial) {
            return false;
        }

        return $testimonial->isPublished();
    }

    public function supports($entityClass)
    {
        return Testimonial::class === $entityClass;
    }
}
