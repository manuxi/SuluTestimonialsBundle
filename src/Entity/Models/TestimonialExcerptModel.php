<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Models;

use Manuxi\SuluSharedToolsBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\TestimonialExcerptModelInterface;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialExcerpt;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialExcerptRepository;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class TestimonialExcerptModel implements TestimonialExcerptModelInterface
{
    use ArrayPropertyTrait;

    private TestimonialExcerptRepository $testimonialExcerptRepository;
    private CategoryManagerInterface $categoryManager;
    private TagManagerInterface $tagManager;
    private MediaRepositoryInterface $mediaRepository;

    public function __construct(
        TestimonialExcerptRepository $testimonialExcerptRepository,
        CategoryManagerInterface $categoryManager,
        TagManagerInterface $tagManager,
        MediaRepositoryInterface $mediaRepository,
    ) {
        $this->testimonialExcerptRepository = $testimonialExcerptRepository;
        $this->categoryManager = $categoryManager;
        $this->tagManager = $tagManager;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function updateTestimonialExcerpt(TestimonialExcerpt $testimonialExcerpt, Request $request): TestimonialExcerpt
    {
        $testimonialExcerpt = $this->mapDataToTestimonialExcerpt($testimonialExcerpt, $request->request->all()['ext']['excerpt']);

        return $this->testimonialExcerptRepository->save($testimonialExcerpt);
    }

    /**
     * @throws EntityNotFoundException
     */
    private function mapDataToTestimonialExcerpt(TestimonialExcerpt $testimonialExcerpt, array $data): TestimonialExcerpt
    {
        $locale = $this->getProperty($data, 'locale');
        if ($locale) {
            $testimonialExcerpt->setLocale($locale);
        }

        $title = $this->getProperty($data, 'title');
        if ($title) {
            $testimonialExcerpt->setTitle($title);
        }

        $more = $this->getProperty($data, 'more');
        if ($more) {
            $testimonialExcerpt->setMore($more);
        }

        $description = $this->getProperty($data, 'description');
        if ($description) {
            $testimonialExcerpt->setDescription($description);
        }

        $categoryIds = $this->getProperty($data, 'categories');
        if ($categoryIds && is_array($categoryIds)) {
            $testimonialExcerpt->removeCategories();
            $categories = $this->categoryManager->findByIds($categoryIds);
            foreach ($categories as $category) {
                $testimonialExcerpt->addCategory($category);
            }
        }

        $tags = $this->getProperty($data, 'tags');
        if ($tags && is_array($tags)) {
            $testimonialExcerpt->removeTags();
            foreach ($tags as $tagName) {
                $testimonialExcerpt->addTag($this->tagManager->findOrCreateByName($tagName));
            }
        }

        $iconIds = $this->getPropertyMulti($data, ['icon', 'ids']);
        if ($iconIds && is_array($iconIds)) {
            $testimonialExcerpt->removeIcons();
            foreach ($iconIds as $iconId) {
                $icon = $this->mediaRepository->findMediaById((int) $iconId);
                if (!$icon) {
                    throw new EntityNotFoundException($this->mediaRepository->getClassName(), $iconId);
                }
                $testimonialExcerpt->addIcon($icon);
            }
        }

        $imageIds = $this->getPropertyMulti($data, ['images', 'ids']);
        if ($imageIds && is_array($imageIds)) {
            $testimonialExcerpt->removeImages();
            foreach ($imageIds as $imageId) {
                $image = $this->mediaRepository->findMediaById((int) $imageId);
                if (!$image) {
                    throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
                }
                $testimonialExcerpt->addImage($image);
            }
        }

        return $testimonialExcerpt;
    }
}
