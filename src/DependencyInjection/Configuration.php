<?php
/**
 * Date: 2018-12-19
 * Time: 23:53
 */

namespace DeliverymanBundle\DependencyInjection;

use DeliverymanBundle\Contract\BatchInstanceInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('deliveryman');

        // is workaround to support symfony/config 4.1 and older
        $rootNode = (method_exists($treeBuilder, 'getRootNode')) ? $treeBuilder->getRootNode() :
            $treeBuilder->root('deliveryman');

        $nodeBuilder = $rootNode->children();

        $nodeBuilder->arrayNode('instances')
            ->info('List of supported of configuration for library.')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->treatNullLike('default')
                        ->example('default')
                        ->info('Type of batch instance to use. When using custom service put type: service.')
                    ->end()

                    ->arrayNode('config')
                        ->variablePrototype()->end()
                        ->info('Configuration settings for this instance.')
                    ->end()

                    ->scalarNode('service')
                        ->defaultNull()
                        ->example('App\\Service\\MyCustomService')
                        ->info(sprintf(
                            'Your local service for handling batches. Must implement %s interface.',
                            BatchInstanceInterface::class
                        ))
                    ->end()
                ->end()
            ->end()
        ->end();

        $nodeBuilder->end();

        return $treeBuilder;
    }

}