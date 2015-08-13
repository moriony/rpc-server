<?php

namespace Moriony\RpcServer\Handler;

use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\Server\AbstractRpcServer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceContainerHandler implements HandlerInterface
{
    /** @var ContainerInterface */
    protected $container;
    protected $service;
    protected $method;

    public function __construct(ContainerInterface $container, $service, $method)
    {
        $this->container = $container;
        $this->method = $method;
        $this->service = $service;
    }

    public function handle(RpcRequestInterface $request)
    {
        if (!$this->container->has($this->service)) {
            throw new InvalidMethodException(AbstractRpcServer::MESSAGE_METHOD_NOT_EXIST);
        }
        $service = $this->container->get($this->service);
        if (!is_object($service) || !is_callable([$service, $this->method])) {
            throw new InvalidMethodException(AbstractRpcServer::MESSAGE_METHOD_NOT_EXIST);
        }
        return call_user_func_array([$service, $this->method], [$request]);
    }
}