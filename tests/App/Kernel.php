<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\App;

use Manuxi\SuluTestimonialsBundle\SuluTestimonialsBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Kernel extends SuluTestKernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        /** @var BundleInterface[] $bundles */
        $bundles = parent::registerBundles();
        $bundles[] = new SuluTestimonialsBundle();

        return $bundles;
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
