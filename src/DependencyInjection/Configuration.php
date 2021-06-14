<?php

namespace WebEtDesign\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('wd_media');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('responsive')
                ->addDefaultsIfNotSet()
                ->children()
                    ->append($this->addResponsiveNode('xl', '1920'))
                    ->append($this->addResponsiveNode('md', '768'))
                    ->append($this->addResponsiveNode('sm', '992'))
                ->end()
                ->end()
                ->arrayNode('categories')
                ->useAttributeAsKey('code')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('label')->cannotBeEmpty()->end()
                        ->append($this->addPreUploadNode())
                        ->arrayNode('formats')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('code')
                            ->arrayPrototype()
                                ->children()
                                    ->append($this->addFormatNode('xl'))
                                    ->append($this->addFormatNode('md'))
                                    ->append($this->addFormatNode('sm'))
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    public function addPreUploadNode()
    {
        $treeBuilder = new TreeBuilder('pre_upload');

        $node = $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('max_width')->defaultValue(1920)->end()
                ->scalarNode('max_height')->defaultValue(1920)->end()
            ->end()
        ;

        return $node;
    }

    public function addFiltersNode()
    {
        $treeBuilder = new TreeBuilder('filters');

        $node = $treeBuilder->getRootNode()
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->useAttributeAsKey('name')
                ->prototype('variable')->end()
            ->end()
        ;

        return $node;
    }

    public function addFormatNode($code)
    {
        $treeBuilder = new TreeBuilder('crop');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->arrayNode($code)
                ->children()
                    ->append($this->addCropNode())
                    ->append($this->addFiltersNode())
                ->end()
        ;

        return $node;
    }

    public function addCropNode()
    {
        $treeBuilder = new TreeBuilder('crop');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('width')->defaultNull()->end()
                ->scalarNode('height')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }

    public function addResponsiveNode($code, $width)
    {
        $treeBuilder = new TreeBuilder('responsive');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->arrayNode($code)
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('label')->defaultValue('wd_media.responsive.'.$code.'.label')->end()
                    ->scalarNode('width')->defaultValue($width)->end()
                ->end()
        ;

        return $node;
    }

}
