<?php

namespace Moriony\RpcServer\Server;

use Moriony\RpcServer\Configuration;
use Moriony\RpcServer\Event\ExceptionEvent;
use Moriony\RpcServer\Event\HttpRequestEvent;
use Moriony\RpcServer\Event\RpcRequestEvent;
use Moriony\RpcServer\Event\MethodCallEvent;
use Moriony\RpcServer\Event\ResponseEvent;
use Moriony\RpcServer\Event\ServiceEvent;
use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\Response\AbstractRpcResponse;
use Moriony\RpcServer\ResponseSerializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Processor;

abstract class AbstractRpcServer
{
    const MESSAGE_METHOD_NOT_EXIST = 'Requested method does not exist.';

    protected $eventDispatcher;
    protected $config;
    protected $container;
    protected $serializer;

    abstract protected function createRequest(Request $request);
    abstract protected function createResponse($body);
    abstract protected function prepareResponseData(RpcRequestInterface $request, $data);
    abstract protected function prepareErrorResponseData(\Exception $e);

    public function __construct(array $config, ContainerInterface $container, EventDispatcherInterface $eventDispatcher, SerializerInterface $serializer)
    {
        $this->container = $container;
        $processor = new Processor();
        $configuration = new Configuration();
        $this->config = $processor->processConfiguration($configuration, [$config]);
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
    }

    final public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param Request $httpRequest
     * @return AbstractRpcResponse
     */
    final public function handleRequest(Request $httpRequest)
    {
        try {
            $this->eventDispatcher->dispatch(Events::EVENT_HTTP_REQUEST, new HttpRequestEvent($httpRequest));

            $request = $this->createRequest($httpRequest);
            $this->eventDispatcher->dispatch(Events::EVENT_RPC_REQUEST, new RpcRequestEvent($request));

            $serviceMethod = $this->getServiceMethod($request);
            $this->eventDispatcher->dispatch(Events::EVENT_RPC_SERVICE, new ServiceEvent($request, $serviceMethod));

            if ($serviceMethod instanceof \Closure) {
                $responseData = $serviceMethod($request);
            } else {
                $responseData = call_user_func_array($serviceMethod, [$request]);
            }
            $responseData = $this->prepareResponseData($request, $responseData);
            $this->eventDispatcher->dispatch(Events::EVENT_RPC_METHOD_CALL, new MethodCallEvent($request, $responseData));
        } catch (\Exception $exception) {
            $this->eventDispatcher->dispatch(Events::EVENT_RPC_EXCEPTION, new ExceptionEvent($httpRequest, $exception));
            $responseData = $this->prepareErrorResponseData($exception);
        }

        $body = $this->serializer->serialize($responseData);
        $response = $this->createResponse($body);
        $this->eventDispatcher->dispatch(Events::EVENT_RPC_RESPONSE, new ResponseEvent($response));
        return $response;
    }

    final protected function getServiceMethod(RpcRequestInterface $request)
    {
        if (!array_key_exists($request->getMethod(), $this->config['methods'])) {
            throw new InvalidMethodException(self::MESSAGE_METHOD_NOT_EXIST);
        }

        $serviceName = $this->config['methods'][$request->getMethod()]['service'];
        if (!$this->container->has($serviceName)) {
            throw new InvalidMethodException(self::MESSAGE_METHOD_NOT_EXIST);
        }

        $service = $this->container->get($serviceName);
        if ($service instanceof \Closure) {
            return $service;
        }

        $method = $this->config['methods'][$request->getMethod()]['method'];
        if (!is_object($service) || !is_callable([$service, $method])) {
            throw new InvalidMethodException(self::MESSAGE_METHOD_NOT_EXIST);
        }
        return [$service, $method];
    }
}