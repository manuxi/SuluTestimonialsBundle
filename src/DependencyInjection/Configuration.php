<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\DependencyInjection;

use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialExcerpt;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialExcerptTranslation;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialSeo;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialSeoTranslation;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialTranslation;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialExcerptRepository;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialExcerptTranslationRepository;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialSeoRepository;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialSeoTranslationRepository;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialTranslationRepository;
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
                    ->arrayNode('testimonial_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(TestimonialTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(TestimonialTranslationRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('testimonial_seo')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(TestimonialSeo::class)->end()
                            ->scalarNode('repository')->defaultValue(TestimonialSeoRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('testimonial_seo_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(TestimonialSeoTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(TestimonialSeoTranslationRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('testimonial_excerpt')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(TestimonialExcerpt::class)->end()
                            ->scalarNode('repository')->defaultValue(TestimonialExcerptRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('testimonial_excerpt_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(TestimonialExcerptTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(TestimonialExcerptTranslationRepository::class)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
