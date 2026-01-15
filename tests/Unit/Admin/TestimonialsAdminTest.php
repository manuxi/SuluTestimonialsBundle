<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Admin;

use Manuxi\SuluTestimonialsBundle\Admin\TestimonialsAdmin;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Content\Infrastructure\Sulu\Admin\ContentViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Localization\Manager\LocalizationManagerInterface;

class TestimonialsAdminTest extends TestCase
{
    use ProphecyTrait;

    public function testConstruct(): void
    {
        $viewBuilderFactory = $this->prophesize(ViewBuilderFactoryInterface::class);
        $contentViewBuilderFactory = $this->prophesize(ContentViewBuilderFactoryInterface::class);
        $activityViewBuilderFactory = $this->prophesize(ActivityViewBuilderFactoryInterface::class);
        $securityChecker = $this->prophesize(SecurityCheckerInterface::class);
        $localizationManager = $this->prophesize(LocalizationManagerInterface::class);

        $admin = new TestimonialsAdmin(
            $viewBuilderFactory->reveal(),
            $contentViewBuilderFactory->reveal(),
            $activityViewBuilderFactory->reveal(),
            $securityChecker->reveal(),
            $localizationManager->reveal()
        );

        $this->assertInstanceOf(Admin::class, $admin);

        $this->assertSame('sulu_testimonials', $admin->getConfigKey());
    }
}
