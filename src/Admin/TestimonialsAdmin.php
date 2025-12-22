<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Admin;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialsSettings;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Localization\Manager\LocalizationManagerInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Content\Infrastructure\Sulu\Admin\ContentViewBuilderFactoryInterface;

class TestimonialsAdmin extends Admin
{
    public const NAV_ITEM = 'sulu_testimonials.testimonials';

    public const LIST_VIEW = 'sulu_testimonials.testimonials.list';
    public const ADD_TABS_VIEW = 'sulu_testimonials.testimonials.add_tabs';
    public const EDIT_TABS_VIEW = 'sulu_testimonials.testimonials.edit_tabs';
    public const EDIT_FORM_DETAILS_VIEW = 'sulu_testimonials.testimonials.edit_form.details';

    // Backward compatibility
    public const EDIT_FORM_VIEW = self::EDIT_TABS_VIEW;

    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly ContentViewBuilderFactoryInterface $contentViewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly LocalizationManagerInterface $localizationManager,
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Testimonial::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $rootNavigationItem = new NavigationItem(static::NAV_ITEM);
            $rootNavigationItem->setIcon('su-comment');
            $rootNavigationItem->setPosition(39);
            $rootNavigationItem->setView(static::LIST_VIEW);

            $testimonialNavigationItem = new NavigationItem(static::NAV_ITEM);
            $testimonialNavigationItem->setPosition(10);
            $testimonialNavigationItem->setView(static::LIST_VIEW);

            $rootNavigationItem->addChild($testimonialNavigationItem);

            $navigationItemCollection->add($rootNavigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales = $this->localizationManager->getLocales();
        $resourceKey = Testimonial::RESOURCE_KEY;

        $formToolbarActions = [];
        $listToolbarActions = [];

        if ($this->securityChecker->hasPermission(Testimonial::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }

        if ($this->securityChecker->hasPermission(Testimonial::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        if ($this->securityChecker->hasPermission(Testimonial::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }

        if ($this->securityChecker->hasPermission(Testimonial::SECURITY_CONTEXT, PermissionTypes::LIVE)) {
            $editDropdownToolbarActions = [
                new ToolbarAction('sulu_admin.save'),
                new ToolbarAction('sulu_admin.publish'),
                new ToolbarAction('sulu_admin.set_unpublished'),
            ];

            if (\count($locales) > 1) {
                $editDropdownToolbarActions[] = new ToolbarAction('sulu_admin.copy_locale');
            }

            $formToolbarActions[] = new DropdownToolbarAction(
                'sulu_admin.edit',
                'su-cog',
                $editDropdownToolbarActions
            );
        } elseif ($this->securityChecker->hasPermission(Testimonial::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
        }

        if ($this->securityChecker->hasPermission(Testimonial::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            // List View
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/testimonials/:locale')
                    ->setResourceKey($resourceKey)
                    ->setListKey(Testimonial::LIST_KEY)
                    ->setTitle('sulu_testimonials.testimonials')
                    ->addListAdapters(['table'])
                    ->addLocales($locales)
                    ->setDefaultLocale($locales[0])
                    ->setAddView(static::ADD_TABS_VIEW)
                    ->setEditView(static::EDIT_TABS_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );

            // Add Tabs View
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_TABS_VIEW, '/testimonials/:locale/add')
                    ->setResourceKey($resourceKey)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            // Edit Tabs View
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_TABS_VIEW, '/testimonials/:locale/:id')
                    ->setResourceKey($resourceKey)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
                    ->setTitleProperty('title')
            );

            // Content Views (Details, SEO, Excerpt)
            $viewBuilders = $this->contentViewBuilderFactory->createViews(
                Testimonial::class,
                static::EDIT_TABS_VIEW,
                static::ADD_TABS_VIEW,
                Testimonial::SECURITY_CONTEXT,
                []
            );

            foreach ($viewBuilders as $viewBuilder) {
                if (method_exists($viewBuilder, 'addToolbarActions') && $viewBuilder->getName() === static::EDIT_FORM_DETAILS_VIEW) {
                    $viewBuilder->addToolbarActions($formToolbarActions);
                }
                $viewCollection->add($viewBuilder);
            }

            // Settings Tab
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_TABS_VIEW . '.settings', '/settings')
                    ->setResourceKey(TestimonialsSettings::RESOURCE_KEY)
                    ->setFormKey(TestimonialsSettings::FORM_KEY)
                    ->setTabTitle('sulu_page.settings')
                    ->setTabOrder(1024)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_TABS_VIEW)
            );
        }
    }

    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Testimonials' => [
                    Testimonial::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                        PermissionTypes::LIVE,
                    ],
                ],
            ],
        ];
    }

    public function getConfigKey(): ?string
    {
        return 'sulu_testimonials';
    }
}
