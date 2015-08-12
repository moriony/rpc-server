<?php
namespace Moriony\RpcServer;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rpc_server');
        $rootNode
            ->children()
                ->arrayNode('methods')
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('service')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('method')
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('extra_data')
                    ->prototype('variable')->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}