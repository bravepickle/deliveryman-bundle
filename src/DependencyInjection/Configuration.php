<?php
/**
 * Date: 2018-12-19
 * Time: 23:53
 */

namespace DeliverymanBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Deliveryman\DependencyInjection\Configuration as LibConfiguration;

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
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->addLibraryInstanceNode())
            ->end()
        ->end();

        $nodeBuilder->end();

//        $defaultInst = $this->addLibraryInstanceNode();
//
//        $nodeBuilder->arrayNode('instances')
//            ->info('List of supported of configurations for library.')
////            ->variablePrototype()->end()
//                ->requiresAtLeastOneElement()
//                ->addDefaultsIfNotSet()
//                ->ignoreExtraKeys()
////                ->defaultValue($defaultInst->)
//                ->addDefaultChildrenIfNoneSet($this->addLibraryInstanceNode())
//                ->children()
//                    ->append($defaultInst)
//                ->end()
//                ->arrayPrototype()
//                ->end()
////                ->append($defaultInst)
//        ->end();

        $nodeBuilder->end();

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeBuilder|NodeDefinition
     */
    protected function addLibraryInstanceNode()
    {
        $nodeName = 'default';
        $config = new LibConfiguration();
        $config->setName($nodeName);

        return $config->buildNodesTree(new TreeBuilder($nodeName));
    }

}