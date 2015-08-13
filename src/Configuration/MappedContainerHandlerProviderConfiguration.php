<?php
namespace Moriony\RpcServer\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class MappedContainerHandlerProviderConfiguration implements ConfigurationInterface
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
                ->arrayNode('map')
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
            ->end();
        return $treeBuilder;
    }
}