<?php

namespace Moriony\RpcServer\Server;

use Moriony\RpcServer\Event\ExceptionEvent;
use Moriony\RpcServer\Event\HttpRequestEvent;
use Moriony\RpcServer\Event\RpcRequestEvent;
use Moriony\RpcServer\Event\MethodCallEvent;
use Moriony\RpcServer\Event\ResponseEvent;
use Moriony\RpcServer\Exception\HandlerNotFoundException;
use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Handler\HandlerInterface;
use Moriony\RpcServer\HandlerProvider\HandlerProviderInterface;
use Moriony\RpcServer\Protocol\ProtocolInterface;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RpcServer
{
    const MESSAGE_METHOD_NOT_EXIST = 'Requested method does not exist.';

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var ProtocolInterface */
    protected $protocol;
    /** @var HandlerInterface[] */
    protected $handlers = [];

    public function __construct(ProtocolInterface $protocol, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->protocol = $protocol;
    }

    final public function getEventDispatcher()
    {
        if (!$this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }
        return $this->eventDispatcher;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param Request $httpRequest
     * @return Response
     */
    final public function handleRequest(Request $httpRequest)
    {
        try {
            $this->eventDispatcher->dispatch(Events::EVENT_HTTP_REQUEST, new HttpRequestEvent($httpRequest));
            $request = $this->getProtocol()->createRequest($httpRequest);
            $this->eventDispatcher->dispatch(Events::EVENT_RPC_REQUEST, new RpcRequestEvent($request));
            $data = $this->handle($request);
            $this->eventDispatcher->dispatch(Events::EVENT_RPC_METHOD_CALL, new MethodCallEvent($request, $data));
            $response = $this->getProtocol()->createResponse($request, $data);
        } catch (\Exception $exception) {
            $this->eventDispatcher->dispatch(Events::EVENT_RPC_EXCEPTION, new ExceptionEvent($httpRequest, $exception));
            $response = $this->getProtocol()->createErrorResponse($exception);
        }
        $this->eventDispatcher->dispatch(Events::EVENT_RPC_RESPONSE, new ResponseEvent($response));
        return $response;
    }

    public function handle(RpcRequestInterface $request)
    {
        if (!array_key_exists($request->getMethod(), $this->handlers)) {
            throw new InvalidMethodException(self::MESSAGE_METHOD_NOT_EXIST);
        }
        return $this->handlers[$request->getMethod()]->handle($request);
    }

    public function addHandler($name, HandlerInterface $handler)
    {
        $this->handlers[$name] = $handler;
        return $this;
    }

    public function getHandler($name)
    {
        if (!array_key_exists($name, $this->handlers)) {
            throw new HandlerNotFoundException();
        }
        return $this->handlers[$name];
    }

    public function addHandlerProvider(HandlerProviderInterface $provider)
    {
        foreach ($provider->provide() as $name => $handler) {
            $this->addHandler($name, $handler);
        }
        return $this;
    }
}