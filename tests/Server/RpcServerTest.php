<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Event\ExceptionEvent;
use Moriony\RpcServer\Event\HttpRequestEvent;
use Moriony\RpcServer\Event\MethodCallEvent;
use Moriony\RpcServer\Event\ResponseEvent;
use Moriony\RpcServer\Event\RpcRequestEvent;
use Moriony\RpcServer\Exception\HandlerNotFoundException;
use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Handler\HandlerInterface;
use Moriony\RpcServer\HandlerProvider\HandlerProviderInterface;
use Moriony\RpcServer\Protocol\ProtocolInterface;
use Moriony\RpcServer\Request\JsonRpcRequest;
use Moriony\RpcServer\Response\JsonRpcResponse;
use Moriony\RpcServer\Server\RpcServer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

class RpcServerTest extends \PHPUnit_Framework_TestCase
{
    /** @var RpcServer $server */
    protected $server;

    public function setUp()
    {
        $this->server = new RpcServer($this->createProtocolMock());
    }

    public function testGetEventDispatcher()
    {
        $dispatcher = new EventDispatcher();
        $server = new RpcServer($this->createProtocolMock(), $dispatcher);

        $this->assertSame($dispatcher, $server->getEventDispatcher());
    }

    public function testGetProtocol()
    {
        $protocol = $this->createProtocolMock();
        $server = new RpcServer($protocol);

        $this->assertSame($protocol, $server->getProtocol());
    }

    public function testAddHandler()
    {
        $handler = $this->createHandlerMock();
        $this->server->addHandler('test', $handler);
        $this->assertSame($handler, $this->server->getHandler('test'));
    }

    public function testGetHandlerException()
    {
        $this->setExpectedException(HandlerNotFoundException::class);
        $this->server->getHandler('test');
    }

    public function testAddHandlerProvider()
    {
        $handler = $this->createHandlerMock();
        $provider = $this->createHandlerProviderMock();

        $provider->expects($this->once())
            ->method('provide')
            ->willReturn(['test' => $handler]);

        $this->server->addHandlerProvider($provider);
        $this->assertSame($handler, $this->server->getHandler('test'));
    }

    public function testHandle()
    {
        $request = $this->createRpcRequestMock(['getMethod']);
        $request->method('getMethod')->willReturn('test');

        $handler = $this->createHandlerMock(['handle']);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request);

        $this->server->addHandler('test', $handler);
        $this->server->handle($request);
    }

    public function testHandleUndefinedMethod()
    {
        $this->setExpectedException(InvalidMethodException::class, 'Requested method does not exist.', -32601);
        $request = $this->createRpcRequestMock();
        $this->server->handle($request);
    }

    public function testHttpRequestEvent()
    {
        $dispatched = false;
        $this->server->getEventDispatcher()->addListener('rpc_server.http_request', function ($e) use(& $dispatched) {
            $this->assertInstanceOf(HttpRequestEvent::class, $e);
            $dispatched = true;
        });

        $this->server->handleRequest(new Request());
        if (!$dispatched) {
            $this->fail('Event rpc_server.http_request must be dispatched.');
        }
    }

    public function testRpcRequestEvent()
    {
        $dispatched = false;
        $this->server->getEventDispatcher()->addListener('rpc_server.json_rpc_request', function ($e) use(& $dispatched) {
            $this->assertInstanceOf(RpcRequestEvent::class, $e);
            $dispatched = true;
        });

        $this->server->handleRequest(new Request());
        if (!$dispatched) {
            $this->fail('Event rpc_server.json_rpc_request must be dispatched.');
        }
    }

    public function testRpcMethodCallEvent()
    {
        $handler = $this->createHandlerMock(['test', 'handle']);
        $handler->method('test');
        $this->server->addHandler('test', $handler);

        $dispatched = false;
        $this->server->getEventDispatcher()->addListener('rpc_server.method_call', function ($e) use(& $dispatched) {
            $this->assertInstanceOf(MethodCallEvent::class, $e);
            $dispatched = true;
        });

        $this->server->handleRequest(new Request());
        if (!$dispatched) {
            $this->fail('Event rpc_server.method_call must be dispatched.');
        }
    }

    public function testExceptionEvent()
    {
        $dispatched = false;
        $this->server->getEventDispatcher()->addListener('rpc_server.exception', function ($e) use (& $dispatched) {
            $this->assertInstanceOf(ExceptionEvent::class, $e);
            $dispatched = true;
        });

        $this->server->handleRequest(new Request());
        if (!$dispatched) {
            $this->fail('Event rpc_server.method_call must be dispatched.');
        }
    }

    public function testRpcResponseEvent()
    {
        $handler = $this->createHandlerMock(['test', 'handle']);
        $handler->method('test');
        $this->server->addHandler('test', $handler);

        $dispatched = false;
        $this->server->getEventDispatcher()->addListener('rpc_server.json_rpc_response', function ($e) use(& $dispatched) {
            $this->assertInstanceOf(ResponseEvent::class, $e);
            $dispatched = true;
        });

        $response = $this->server->handleRequest(new Request());
        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        if (!$dispatched) {
            $this->fail('Event rpc_server.json_rpc_response must be dispatched.');
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HandlerInterface
     */
    public function createHandlerMock(array $methods = [])
    {
        return $this->getMockBuilder(HandlerInterface::class)
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProtocolInterface
     */
    public function createProtocolMock()
    {
        $mock = $this->getMockBuilder(ProtocolInterface::class)
            ->setMethods(['createRequest', 'createResponse', 'createErrorResponse', 'getName'])
            ->getMock();

        $rpcRequest = $this->createRpcRequestMock();
        $rpcRequest->method('getMethod')->willReturn('test');

        $mock->method('getName')
            ->willReturn('test');

        $mock->method('createRequest')
            ->willReturn($rpcRequest);

        $mock->method('createResponse')
            ->willReturn(new JsonRpcResponse());

        $mock->method('createErrorResponse')
            ->willReturn(new JsonRpcResponse());

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HandlerProviderInterface
     */
    public function createHandlerProviderMock()
    {
        return $this->getMockBuilder(HandlerProviderInterface::class)
            ->setMethods(['provide'])
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JsonRpcRequest
     */
    public function createRpcRequestMock(array $methods = [])
    {
        return $this->getMockBuilder(JsonRpcRequest::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
