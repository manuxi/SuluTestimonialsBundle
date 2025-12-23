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
     * @param $entityClass
     * @param $id
     * @param $locale
     * @param null $object
     * @return mixed[]
     */
    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        return [
            '_controller' => TestimonialsController::class . '::indexAction',
            'testimonial' => $this->repository->findById((int) $id),
        ];
    }

    public function isPublished($entityClass, $id, $locale): bool
    {
        $testimonial = $this->repository->findById((int) $id);
        if (!$this->supports($entityClass) || !$testimonial instanceof Testimonial) {
            return false;
        }

        foreach ($testimonial->getDimensionContents() as $dimensionContent) {
            if ($dimensionContent->getLocale() === $locale && $dimensionContent->getStage() === \Sulu\Content\Domain\Model\DimensionContentInterface::STAGE_LIVE) {
                return $dimensionContent->getWorkflowPlace() === \Sulu\Content\Domain\Model\WorkflowInterface::WORKFLOW_PLACE_PUBLISHED;
            }
        }

        return false;
    }

    public function supports($entityClass)
    {
        return Testimonial::class === $entityClass;
    }
}
