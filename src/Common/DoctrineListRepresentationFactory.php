<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Common;

use Manuxi\SuluTestimonialsBundle\Repository\TestimonialTranslationRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRestHelperInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class DoctrineListRepresentationFactory
{
    private RestHelperInterface $restHelper;
    private ListRestHelperInterface $listRestHelper;
    private DoctrineListBuilderFactory $listBuilderFactory;
    private FieldDescriptorFactoryInterface $fieldDescriptorFactory;
    private WebspaceManagerInterface $webspaceManager;
    private TestimonialTranslationRepository $testimonialTranslationRepository;
    private MediaManagerInterface $mediaManager;

    public function __construct(
        RestHelperInterface $restHelper,
        ListRestHelperInterface $listRestHelper,
        DoctrineListBuilderFactory $listBuilderFactory,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        WebspaceManagerInterface $webspaceManager,
        TestimonialTranslationRepository $testimonialTranslationRepository,
        MediaManagerInterface $mediaManager
    ) {
        $this->restHelper = $restHelper;
        $this->listRestHelper = $listRestHelper;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->webspaceManager = $webspaceManager;
        $this->testimonialTranslationRepository = $testimonialTranslationRepository;
        $this->mediaManager = $mediaManager;
    }

    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array  $includedFields = []
    ): PaginatedRepresentation
    {
        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($resourceKey);

        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }

        foreach ($includedFields as $field) {
            $listBuilder->addSelectField($fieldDescriptors[$field]);
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

        return new PaginatedRepresentation(
            $list,
            $resourceKey,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );
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
            if (\array_key_exists('image', $element)
                && $element['image']
                && \array_key_exists($element['image'], $images)
            ) {
                $listeElements[$key]['image'] = $images[$element['image']];
            }
        }

        return $listeElements;
    }

    private function addGhostLocaleToListElements(array $listeElements, ?string $currentLocale): array {
        $availableLocales = $locales = $this->webspaceManager->getAllLocales();
        $localesCount = count($availableLocales);
        if (($key = array_search($currentLocale, $locales)) !== false) {
            unset($locales[$key]);
        }

        $ids = array_filter(array_column($listeElements, 'id'));

        foreach($locales as $locale) {
            $missingLocales = $this->testimonialTranslationRepository->findMissingLocaleByIds($ids, $locale, $localesCount);
            foreach($missingLocales as $missingLocale) {
                foreach ($listeElements as $key => $element) {
                    if ($element['id'] === (int)$missingLocale['testimonial'] && !array_key_exists('ghostLocale', $element)) {
                        $listeElements[$key]['ghostLocale'] = $locale;
//                        $listeElements[$key]['localizationState'] = [
//                            'state' => 'ghost',
//                            'locale' => $locale
//                        ];
                    }
                }
            }
        }

        return $listeElements;
    }
}
