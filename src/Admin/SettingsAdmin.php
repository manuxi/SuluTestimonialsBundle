<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Admin;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialsSettings;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class SettingsAdmin extends Admin
{
    public const TAB_VIEW = 'sulu_testimonials.config';
    public const FORM_VIEW = 'sulu_testimonials.config.form';
    public const NAV_ITEM = 'sulu_testimonials.config.title.navi';

    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(TestimonialsSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $module = $navigationItemCollection->get(TestimonialsAdmin::NAV_ITEM);
            $settings = new NavigationItem(static::NAV_ITEM);
            $settings->setPosition(20);
            $settings->setView(static::TAB_VIEW);

            $module->addChild($settings);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission(TestimonialsSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
            // sulu will only load the existing entity if the path of the form includes an id attribute
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::TAB_VIEW, '/testimonials-settings/:id')
                    ->setResourceKey(TestimonialsSettings::RESOURCE_KEY)
                    ->setAttributeDefault('id', '-')
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::FORM_VIEW, '/config')
                    ->setResourceKey(TestimonialsSettings::RESOURCE_KEY)
                    ->setFormKey(TestimonialsSettings::FORM_KEY)
                    ->setTabTitle('sulu_testimonials.config.tab')
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                    ->setParent(static::TAB_VIEW)
            );
        }
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Testimonials' => [
                    TestimonialsSettings::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::EDIT,
                    ],
                ],
            ],
        ];
    }

    public function getConfigKey(): ?string
    {
        return 'sulu_testimonials.config.title';
    }
}