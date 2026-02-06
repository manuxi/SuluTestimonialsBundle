<?php
declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Common;

use Manuxi\SuluTestimonialsBundle\Repository\TestimonialDimensionContentRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRestHelperInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use function array_key_exists;

class DoctrineListRepresentationFactory
{
    public function __construct(
        private RestHelperInterface                   $restHelper,
        private ListRestHelperInterface               $listRestHelper,
        private DoctrineListBuilderFactory            $listBuilderFactory,
        private FieldDescriptorFactoryInterface       $fieldDescriptorFactory,
        private WebspaceManagerInterface              $webspaceManager,
        private TestimonialDimensionContentRepository $testimonialDimensionContentRepository,
        private MediaManagerInterface                 $mediaManager
    )
    {
    }

    public function createDoctrineListRepresentation(
        string  $resourceKey,
        array   $filters = [],
        array   $parameters = [],
        ?string $listKey = null
    ): PaginatedRepresentation
    {
        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($listKey ?? $resourceKey);
        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $listBuilder->setIdField($fieldDescriptors['id']);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);
        if (isset($fieldDescriptors['image'])) {
            $listBuilder->addSelectField($fieldDescriptors['image']);
        }
        if (isset($fieldDescriptors['workflowPlace'])) {
            $listBuilder->addSelectField($fieldDescriptors['workflowPlace']);
        }
        if (isset($fieldDescriptors['publishedState'])) {
            $listBuilder->addSelectField($fieldDescriptors['publishedState']);
        }
        if (isset($fieldDescriptors['published'])) {
            $listBuilder->addSelectField($fieldDescriptors['published']);
        }
        if (isset($fieldDescriptors['livePublished'])) {
            $listBuilder->addSelectField($fieldDescriptors['livePublished']);
        }
        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }
        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }
        $list = $listBuilder->execute();
        // sort the items to reflect the order of the given ids if the list was requested to include specific ids
        $requestedIds = $this->listRestHelper->getIds();
        if (null !== $requestedIds) {
            $idPositions = array_flip($requestedIds);
            usort($list, function ($a, $b) use ($idPositions) {
                return $idPositions[$a['id']] - $idPositions[$b['id']];
            });
        }
        $list = $this->addGhostLocaleToListElements($list, $parameters['locale'] ?? null);
        $list = $this->addImagesToListElements($list, $parameters['locale'] ?? null);
        $list = $this->addPublishStateToListElements($list, $listKey);
        return new PaginatedRepresentation(
            $list,
            $resourceKey,
            (int)$listBuilder->getCurrentPage(),
            (int)$listBuilder->getLimit(),
            (int)$listBuilder->count()
        );
    }

    private function addGhostLocaleToListElements(array $listeElements, ?string $currentLocale): array
    {
        $availableLocales = $locales = $this->webspaceManager->getAllLocales();
        $localesCount = count($availableLocales);
        if (($key = array_search($currentLocale, $locales)) !== false) {
            unset($locales[$key]);
        }
        $ids = array_filter(array_column($listeElements, 'id'));
        foreach ($locales as $locale) {
            $missingLocales = $this->testimonialDimensionContentRepository->findMissingLocaleByIds($ids, $locale, $localesCount);
            foreach ($missingLocales as $missingLocale) {
                foreach ($listeElements as $key => $element) {
                    if ($element['id'] === $missingLocale['testimonial'] && !array_key_exists('ghostLocale', $element)) {
                        $listeElements[$key]['ghostLocale'] = $locale;
                    }
                }
            }
        }
        return $listeElements;
    }

    /**
     * @param mixed[]
     * @param string|null $locale
     * @return array
     */
    private function addImagesToListElements(array $listeElements, ?string $locale): array
    {
        $ids = array_filter(array_column($listeElements, 'image'));
        $images = $this->mediaManager->getFormatUrls($ids, $locale);
        foreach ($listeElements as $key => $element) {
            if (
                array_key_exists('image', $element)
                && $element['image']
                && array_key_exists($element['image'], $images)
            ) {
                $listeElements[$key]['image'] = $images[$element['image']];
            }
        }
        return $listeElements;
    }

    private function addPublishStateToListElements(array $listElements, ?string $listKey = null): array
    {
        foreach ($listElements as $key => $element) {
            if ('testimonials_published' === $listKey) {
                $listElements[$key]['publishedState'] = true;
                $listElements[$key]['workflowPlace'] = 'published';
                continue;
            }
            if (empty($element['published']) && !empty($element['livePublished'])) {
                $listElements[$key]['published'] = $element['livePublished'];
            }
            $workflowPlace = $element['publishedState'] ?? $element['workflowPlace'] ?? null;
            $listElements[$key]['workflowPlace'] = $workflowPlace;
            $listElements[$key]['publishedState'] = 'published' === $workflowPlace;
        }
        return $listElements;
    }
}