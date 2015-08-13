<?php

namespace Moriony\RpcServer\Server;

use Moriony\RpcServer\Event\ExceptionEvent;
use Moriony\RpcServer\Event\HttpRequestEvent;
use Moriony\RpcServer\Event\RpcRequestEvent;
use Moriony\RpcServer\Event\MethodCallEvent;
use Moriony\RpcServer\Event\ResponseEvent;
use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Handler\HandlerInterface;
use Moriony\RpcServer\HandlerProvider\HandlerProviderInterface;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\ResponseSerializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractRpcServer
{
    const MESSAGE_METHOD_NOT_EXIST = 'Requested method does not exist.';

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var SerializerInterface */
    protected $serializer;
    /** @var HandlerInterface[] */
    protected $handlers;

    /** @return RpcRequestInterface */
    abstract protected function createRequest(Request $request);
    abstract protected function createResponse($body);
    abstract protected function prepareResponseData(RpcRequestInterface $request, $data);
    abstract protected function prepareErrorResponseData(\Exception $e);

    public function __construct(EventDispatcherInterface $eventDispatcher, SerializerInterface $serializer)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
    }

    final public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param Request $httpRequest
     * @return Response
     */
    final public function handleRequest(Request $httpRequest)
    {
        try {
            $this->eventDispatcher->dispatch(Events::EVENT_HTTP_REQUEST, new HttpRequestEvent($httpRequest));

            $request = $this->createRequest($httpRequest);
            $this->eventDispatcher->dispatch(Events::EVENT_RPC_REQUEST, new RpcRequestEvent($request));

            if (!array_key_exists($request->getMethod(), $this->handlers)) {
                throw new InvalidMethodException(self::MESSAGE_METHOD_NOT_EXIST);
            }
            $responseData = $this->handlers[$request->getMethod()]->handle($request);
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

    public function addHandler($name, HandlerInterface $handler)
    {
        $this->handlers[$name] = $handler;
        return $this;
    }

    public function addHandlerProvider(HandlerProviderInterface $provider)
    {
        foreach ($provider->provide() as $name => $handler) {
            $this->addHandler($name, $handler);
        }
        return $this;
    }
}