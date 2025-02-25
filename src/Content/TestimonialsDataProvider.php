<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Content;

use Countable;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Admin\TestimonialsAdmin;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Service\TestimonialRatingSelect;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderResult;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TestimonialsDataProvider extends BaseDataProvider
{
    private int $defaultLimit = 12;

    public function __construct(
        DataProviderRepositoryInterface $repository,
        ArraySerializerInterface $serializer,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private TestimonialRatingSelect $ratingSelect
    ) {
        parent::__construct($repository, $serializer);
    }

    private function getTypes(): array
    {
        $types = $this->ratingSelect->getValues();
        $return = [];
        foreach ($types as $key => $values) {
            $temp = [];
            $temp['type'] = $values['name'];
            $temp['title'] = $values['title'];
            $return[] = $temp;
        }
        return $return;
    }

    public function getConfiguration(): ProviderConfigurationInterface
    {
        if (null === $this->configuration) {
            $this->configuration = self::createConfigurationBuilder()
                ->enableLimit()
                ->enablePagination()
                ->enablePresentAs()
                ->enableCategories()
                ->enableTags()
                ->enableTypes($this->getTypes())
                ->enableSorting($this->getSorting())
                ->enableView(TestimonialsAdmin::EDIT_FORM_VIEW, ['id' => 'id'])
                ->getConfiguration();
        }

        return parent::getConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function resolveResourceItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null
    ) {

        $locale = $options['locale'];
        $request = $this->requestStack->getCurrentRequest();
        $options['page'] = $request->get('p');
        $testimonials = $this->entityManager->getRepository(Testimonial::class)->findByFilters($filters, $page, $pageSize, $limit, $locale, $options);
        return new DataProviderResult($testimonials, $this->entityManager->getRepository(Testimonial::class)->hasNextPage($filters, $page, $pageSize, $limit, $locale, $options));
    }

    /**
     * @param mixed[] $data
     * @return array
     */
    protected function decorateDataItems(array $data): array
    {
        return \array_map(
            static function ($item) {
                return new TestimonialDataItem($item);
            },
            $data
        );
    }

    /**
     * Returns flag "hasNextPage".
     * It combines the limit/query-count with the page and page-size.
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     * @param Countable $queryResult
     * @param int|null $limit
     * @param int $page
     * @param int|null $pageSize
     * @return bool
     */
    private function hasNextPage(Countable $queryResult, ?int $limit, int $page, ?int $pageSize): bool
    {
        $count = $queryResult->count();

        if (null === $pageSize || $pageSize > $this->defaultLimit) {
            $pageSize = $this->defaultLimit;
        }

        $offset = ($page - 1) * $pageSize;
        if ($limit && $offset + $pageSize > $limit) {
            return false;
        }

        return $count > ($page * $pageSize);
    }

    private function getSorting(): array
    {
        return [
            ['column' => 'translation.name', 'title' => 'sulu_testimonials.title'],
            ['column' => 'translation.text', 'title' => 'sulu_testimonials.text'],
            ['column' => 'translation.published_at', 'title' => 'sulu_testimonials.published_at'],
            ['column' => 'translation.rating', 'title' => 'sulu_testimonials.rating'],
        ];
    }

}
