<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Admin;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AutomationBundle\Admin\AutomationAdmin;
use Sulu\Bundle\AutomationBundle\Admin\View\AutomationViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

/**
 * Adds the automation tab to the testimonial edit view, only loaded when sulu/automation-bundle is installed.
 */
class TestimonialsAutomationAdmin extends Admin
{
    public function __construct(
        private readonly AutomationViewBuilderFactoryInterface $automationViewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
    ) {
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if ($viewCollection->has(TestimonialsAdmin::EDIT_TABS_VIEW)
            && $this->securityChecker->hasPermission(AutomationAdmin::SECURITY_CONTEXT, PermissionTypes::EDIT)
        ) {
            $viewCollection->add(
                $this->automationViewBuilderFactory->createTaskListViewBuilder(
                    TestimonialsAdmin::EDIT_TABS_VIEW . '.automation',
                    '/automation',
                    Testimonial::class,
                )
                    ->setTabOrder(4096)
                    ->setParent(TestimonialsAdmin::EDIT_TABS_VIEW),
            );
        }
    }
}
