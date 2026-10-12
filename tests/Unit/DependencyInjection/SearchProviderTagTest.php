<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Without the tag the search providers never run: "cmsig:seal:reindex" skipped the testimonials and only a publish
 * wrote them into the index.
 */
class SearchProviderTagTest extends TestCase
{
    /**
     * @dataProvider providers
     */
    public function testSearchProviderIsTaggedForTheReindex(string $serviceId): void
    {
        $config = Yaml::parseFile(__DIR__ . '/../../../src/Resources/config/services.yaml');
        $tags = array_column($config['services'][$serviceId]['tags'] ?? [], 'name');

        $this->assertContains('cmsig_seal.reindex_provider', $tags);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function providers(): iterable
    {
        yield 'admin' => ['sulu_testimonials.search.admin_provider'];
        yield 'website' => ['sulu_testimonials.search.website_provider'];
    }
}
