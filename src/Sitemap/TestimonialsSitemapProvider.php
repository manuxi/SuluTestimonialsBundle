<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Sitemap;

use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class TestimonialsSitemapProvider implements SitemapProviderInterface
{
    private array $locales = [];

    public function __construct(
        private readonly TestimonialRepository $repository,
        private readonly WebspaceManagerInterface $webspaceManager
    ) {}

    public function build($page, $scheme, $host)
    {
        $locale = $this->getLocaleByHost($host);

        $result = [];
        foreach ($this->findTestimonials($locale,self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE) as $abbr) {
            $result[] = new SitemapUrl(
                $scheme . '://' . $host . $abbr->getRoutePath(),
                $abbr->getLocale(),
                $abbr->getLocale(),
                $abbr->getChanged()
            );
        }

        return $result;
    }

    public function createSitemap($scheme, $host)
    {
        return new Sitemap($this->getAlias(), $this->getMaxPage($scheme, $host));
    }

    public function getAlias()
    {
        return 'testimonials';
    }

    /**
     * @TODO: count method in repo
     */
    public function getMaxPage($scheme, $host)
    {
        $locale = $this->getLocaleByHost($host);
        return ceil($this->repository->countForSitemap($locale) / self::PAGE_SIZE);
    }

    private function getLocaleByHost($host) {
        if(!\array_key_exists($host, $this->locales)) {
            $portalInformation = $this->webspaceManager->getPortalInformations();
            foreach ($portalInformation as $hostName => $portal) {
                if($hostName === $host || $portal->getHost() === $host) {
                    $this->locales[$host] = $portal->getLocale();
                }
            }
        }
        return $this->locales[$host];
    }

    private function findTestimonials(string $locale, int $limit = null, int $offset = null): array
    {
        return $this->repository->findAllForSitemap($locale, $limit, $offset);
    }
}
