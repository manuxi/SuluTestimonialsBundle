<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Entity\Models;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialSeo;
use Manuxi\SuluTestimonialsBundle\Entity\Interfaces\TestimonialSeoModelInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialSeoRepository;
use Symfony\Component\HttpFoundation\Request;

class TestimonialSeoModel implements TestimonialSeoModelInterface
{
    use ArrayPropertyTrait;

    private TestimonialSeoRepository $testimonialSeoRepository;

    public function __construct(
        TestimonialSeoRepository $testimonialSeoRepository
    ) {
        $this->testimonialSeoRepository = $testimonialSeoRepository;
    }

    public function updateTestimonialSeo(TestimonialSeo $testimonialSeo, Request $request): TestimonialSeo
    {
        $testimonialSeo = $this->mapDataToTestimonialSeo($testimonialSeo, $request->request->all()['ext']['seo']);
        return $this->testimonialSeoRepository->save($testimonialSeo);
    }

    private function mapDataToTestimonialSeo(TestimonialSeo $entity, array $data): TestimonialSeo
    {
        $locale = $this->getProperty($data, 'locale');
        if ($locale) {
            $entity->setLocale($locale);
        }
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $entity->setTitle($title);
        }
        $description = $this->getProperty($data, 'description');
        if ($description) {
            $entity->setDescription($description);
        }
        $keywords = $this->getProperty($data, 'keywords');
        if ($keywords) {
            $entity->setKeywords($keywords);
        }
        $canonicalUrl = $this->getProperty($data, 'canonicalUrl');
        if ($canonicalUrl) {
            $entity->setCanonicalUrl($canonicalUrl);
        }
        $noIndex = $this->getProperty($data, 'noIndex');
        if ($noIndex) {
            $entity->setNoIndex($noIndex);
        }
        $noFollow = $this->getProperty($data, 'noFollow');
        if ($noFollow) {
            $entity->setNoFollow($noFollow);
        }
        $hideInSitemap = $this->getProperty($data, 'hideInSitemap');
        if ($hideInSitemap) {
            $entity->setHideInSitemap($hideInSitemap);
        }
        return $entity;
    }
}
