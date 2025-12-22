<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\DependencyInjection;

use Manuxi\SuluTestimonialsBundle\Admin\TestimonialsAdmin;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SuluTestimonialsExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        // $loader->load('services.xml');
        // $loader->load('controller.xml');

        $yamlLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $yamlLoader->load('services.yaml');
        $yamlLoader->load('controller.yaml');

        if ($container->hasParameter('kernel.bundles')) {
            /** @var string[] $bundles */
            $bundles = $container->getParameter('kernel.bundles');

            if (\array_key_exists('SuluAutomationBundle', $bundles)) {
                $loader->load('automation.xml');
            }
        }

        $this->configurePersistence($config['objects'], $container);
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('sulu_search')) {
            $container->prependExtensionConfig(
                'sulu_search',
                [
                    'admin' => [
                        'resources' => [
                            Testimonial::RESOURCE_KEY => [
                                'name' => 'sulu_testimonials.testimonials',
                                'icon' => 'su-comment',
                                'route' => [
                                    'name' => TestimonialsAdmin::EDIT_FORM_VIEW,
                                    'resultToRoute' => [
                                        'resourceId' => 'id',
                                        'locale' => 'locale',
                                    ],
                                ],
                                'securityContext' => Testimonial::SECURITY_CONTEXT,
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_seo')) {
            $container->prependExtensionConfig(
                'sulu_seo',
                [
                    'content' => [
                        'types' => [
                            Testimonial::TEMPLATE_TYPE => [
                                'template_driver' => true,
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_excerpt')) {
            $container->prependExtensionConfig(
                'sulu_excerpt',
                [
                    'content' => [
                        'types' => [
                            Testimonial::TEMPLATE_TYPE => [
                                'template_driver' => true,
                            ],
                        ],
                    ],
                ]
            );
        }

        /*        if ($container->hasExtension('sulu_route')) {
                    $container->prependExtensionConfig(
                        'sulu_route',
                        [
                            'mappings' => [
                                Testimonial::class => [
                                    'generator' => 'schema',
                                    'options' => [
                                        // @TODO: works not yet as expected, does not translate correctly
                                        // see https://github.com/sulu/sulu/pull/5920
                                        'route_schema' => '/{translator.trans("sulu_testimonials.testimonials")}/{implode("-", object)}',
                                    ],
                                    'resource_key' => Testimonial::RESOURCE_KEY,
                                ],
                            ],
                        ]
                    );
                }*/

        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'lists' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/lists',
                        ],
                    ],
                    'forms' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/forms',
                        ],
                    ],
                    'templates' => [
                        Testimonial::TEMPLATE_TYPE => [
                            'default_type' => Testimonial::TEMPLATE_TYPE,
                            'directories' => [
                                __DIR__ . '/../Resources/config/templates',
                            ],
                        ],
                    ],
                    'resources' => [
                        'testimonials' => [
                            'routes' => [
                                'list' => 'sulu_testimonials.get_testimonials',
                                'detail' => 'sulu_testimonials.get_testimonial',
                            ],
                        ],
                        'testimonials-settings' => [
                            'routes' => [
                                'detail' => 'sulu_testimonials.get_testimonials-settings',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'testimonial_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Testimonial::RESOURCE_KEY,
                                'view' => [
                                    'name' => TestimonialsAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Testimonial::LIST_KEY,
                                        'display_properties' => [
                                            'name',
                                        ],
                                        'icon' => 'su-tag-pen',
                                        'label' => 'sulu_testimonials.testimonials_selection_label',
                                        'overlay_title' => 'sulu_testimonials.select_testimonial',
                                    ],
                                ],
                            ],
                        ],
                        'single_selection' => [
                            'single_testimonial_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Testimonial::RESOURCE_KEY,
                                'view' => [
                                    'name' => TestimonialsAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Testimonial::LIST_KEY,
                                        'display_properties' => [
                                            'name',
                                        ],
                                        'icon' => 'su-tag-pen',
                                        'empty_text' => 'sulu_testimonials.no_testimonial_selected',
                                        'overlay_title' => 'sulu_testimonials.select_testimonial',
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'name',
                                        'search_properties' => [
                                            'name',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }

        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['paths' => [__DIR__ . '/../Resources/config/translations/']],
        ]);
    }
}
