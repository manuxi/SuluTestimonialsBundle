<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\DependencyInjection;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialDimensionContentRepository;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sulu_testimonials');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
            ->arrayNode('objects')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('testimonial')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('model')->defaultValue(Testimonial::class)->end()
            ->scalarNode('repository')->defaultValue(TestimonialRepository::class)->end()
            ->end()
            ->end()
            ->arrayNode('testimonial_dimension_content')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('model')->defaultValue(TestimonialDimensionContent::class)->end()
            ->scalarNode('repository')->defaultValue(TestimonialDimensionContentRepository::class)->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
