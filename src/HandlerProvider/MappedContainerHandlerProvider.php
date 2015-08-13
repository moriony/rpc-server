<?php

namespace Moriony\RpcServer\HandlerProvider;

use Moriony\RpcServer\Configuration\MappedContainerHandlerProviderConfiguration;
use Moriony\RpcServer\Handler\ServiceContainerHandler;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MappedContainerHandlerProvider implements HandlerProviderInterface
{
    protected $container;
    protected $config;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $processor = new Processor();
        $configuration = new MappedContainerHandlerProviderConfiguration();
        $this->config = $processor->processConfiguration($configuration, [$config]);
    }

    /**
     * @return ServiceContainerHandler[]
     */
    public function provide()
    {
        $handlers = [];
        foreach ($this->config['map'] as $name => $map) {
            $handlers[$name] = new ServiceContainerHandler($this->container, $map['service'], $map['method']);
        }
        return $handlers;
    }
}