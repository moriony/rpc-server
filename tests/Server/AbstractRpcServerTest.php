<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Handler\HandlerInterface;
use Moriony\RpcServer\HandlerProvider\HandlerProviderInterface;
use Moriony\RpcServer\Request\JsonRpcRequest;
use Moriony\RpcServer\Server\AbstractRpcServer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Moriony\RpcServer\Response\JsonRpcResponse;

class AbstractRpcServerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEventDispatcher()
    {
        $dispatcher = $this->createEventDispatcherMock();
        $server = $this->getMockBuilder('Moriony\RpcServer\Server\AbstractRpcServer')
            ->setConstructorArgs([$dispatcher, $this->createSerializerMock()])
            ->getMock();

        $this->assertSame($dispatcher, $server->getEventDispatcher());
    }

    public function testAddHandlerProvider()
    {
        $server = $this->createServerMock(new EventDispatcher(), $this->createSerializerMock(), ['addHandler']);
        $handler = $this->createHandlerMock();
        $provider = $this->createHandlerProviderMock();
        $provider->expects($this->once())
            ->method('provide')
            ->willReturn(['test' => $handler]);

        $server->expects($this->once())
            ->method('addHandler')
            ->with('test', $handler);

        $server->addHandlerProvider($provider);
    }

    public function testHandle()
    {
        $server = $this->createServerMock(new EventDispatcher(), $this->createSerializerMock());

        $request = $this->createRpcRequestMock(['getMethod']);
        $request->method('getMethod')->willReturn('test');

        $handler = $this->createHandlerMock(['handle']);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request);

        $server->addHandler('test', $handler);
        $server->handle($request);
    }

    public function testHandleUndefinedMethod()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidMethodException', 'Requested method does not exist.', -32601);
        $server = $this->createServerMock(new EventDispatcher(), $this->createSerializerMock());
        $request = $this->createRpcRequestMock();
        $server->handle($request);
    }

    public function testHttpRequestEvent()
    {
        $server = $this->createServerMock(new EventDispatcher(), $this->createSerializerMock());
        $server->method('createResponse')->willReturn(new JsonRpcResponse());

        $request = new Request();
        $dispatched = false;
        $server->getEventDispatcher()->addListener('rpc_server.http_request', function ($e) use($request, & $dispatched) {
            $this->assertInstanceOf('Moriony\RpcServer\Event\HttpRequestEvent', $e);
            $this->assertSame($request, $e->getRequest());
            $dispatched = true;
        });

        $server->handleRequest($request);
        if (!$dispatched) {
            $this->fail('Event rpc_server.http_request must be dispatched.');
        }
    }

    public function testRpcRequestEvent()
    {
        $server = $this->createServerMock(new EventDispatcher(), $this->createSerializerMock());
        $request = $this->createRpcRequestMock();
        $server->method('createRequest')->willReturn($request);
        $server->method('createResponse')->willReturn(new JsonRpcResponse());

        $dispatched = false;
        $server->getEventDispatcher()->addListener('rpc_server.json_rpc_request', function ($e) use($request, & $dispatched) {
            $this->assertInstanceOf('Moriony\RpcServer\Event\RpcRequestEvent', $e);
            $this->assertSame($request, $e->getRequest());
            $dispatched = true;
        });

        $server->handleRequest(new Request());
        if (!$dispatched) {
            $this->fail('Event rpc_server.json_rpc_request must be dispatched.');
        }
    }

    public function testRpcMethodCallEvent()
    {
        $server = $this->createServerMock(new EventDispatcher(), $this->createSerializerMock(), ['handle', 'prepareResponseData']);
        $request = $this->createRpcRequestMock();
        $server->method('createRequest')->willReturn($request);
        $server->method('handle')->willReturn('test_result');
        $server->method('prepareResponseData')->willReturn('test_result');
        $server->method('createResponse')->willReturn(new JsonRpcResponse());
        
        $dispatched = false;
        $server->getEventDispatcher()->addListener('rpc_server.method_call', function ($e) use($request, & $dispatched) {
            $this->assertInstanceOf('Moriony\RpcServer\Event\MethodCallEvent', $e);
            $this->assertSame($request, $e->getRequest());
            $this->assertSame('test_result', $e->getResult());
            $dispatched = true;
        });

        $server->handleRequest(new Request());
        if (!$dispatched) {
            $this->fail('Event rpc_server.method_call must be dispatched.');
        }
    }

    public function testExceptionEvent()
    {
        $server = $this->createServerMock(new EventDispatcher(), $this->createSerializerMock(), ['handle', 'prepareResponseData']);
        $request = $this->createRpcRequestMock();
        $server->method('createRequest')->willReturn($request);
        $exception = new \Exception();
        $server->method('handle')->willThrowException($exception);
        $server->method('prepareResponseData')->willReturn('test_result');
        $server->method('createResponse')->willReturn(new JsonRpcResponse());

        $dispatched = false;
        $server->getEventDispatcher()->addListener('rpc_server.exception', function ($e) use($exception, & $dispatched) {
            $this->assertInstanceOf('Moriony\RpcServer\Event\ExceptionEvent', $e);
            $this->assertSame($exception, $e->getException());
            $dispatched = true;
        });

        $server->handleRequest(new Request());
        if (!$dispatched) {
            $this->fail('Event rpc_server.method_call must be dispatched.');
        }
    }

    public function testRpcResponseEvent()
    {
        $server = $this->createServerMock(new EventDispatcher(), $this->createSerializerMock(), ['handle', 'prepareResponseData']);
        $response = new JsonRpcResponse();
        $server->method('createResponse')->willReturn($response);

        $dispatched = false;
        $server->getEventDispatcher()->addListener('rpc_server.json_rpc_response', function ($e) use($response, & $dispatched) {
            $this->assertInstanceOf('Moriony\RpcServer\Event\ResponseEvent', $e);
            $this->assertSame($response, $e->getresponse());
            $dispatched = true;
        });

        $this->assertSame($response, $server->handleRequest(new Request()));
        if (!$dispatched) {
            $this->fail('Event rpc_server.json_rpc_response must be dispatched.');
        }
    }

    public function testSerialize()
    {
        $serializer = $this->createSerializerMock();
        $serializer->expects($this->once())
            ->method('serialize')
            ->with('test_data')
            ->willReturn('test_serialized_data');

        $server = $this->createServerMock(new EventDispatcher(), $serializer);
        $server->method('prepareErrorResponseData')->willReturn('test_data');
        $server->expects($this->once())
            ->method('createResponse')
            ->with('test_serialized_data')
            ->willReturn(new JsonRpcResponse());

        $server->handleRequest(new Request());
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SerializerInterface
     */
    public function createSerializerMock()
    {
        return $this->getMockBuilder('Moriony\RpcServer\ResponseSerializer\SerializerInterface')
            ->getMock();
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HandlerInterface
     */
    public function createHandlerMock(array $methods = [])
    {
        return $this->getMockBuilder('Moriony\RpcServer\Handler\HandlerInterface')
            ->setMethods($methods)
            ->getMock();
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HandlerProviderInterface
     */
    public function createHandlerProviderMock()
    {
        return $this->getMockBuilder('Moriony\RpcServer\HandlerProvider\HandlerProviderInterface')
            ->setMethods(['provide'])
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HandlerProviderInterface
     */
    public function createResponseMock()
    {
        return $this->getMockBuilder('Moriony\RpcServer\Response\JsonRpcResponse')
            ->setMethods(['provide'])
            ->getMock();
    }

    /**
     * @return AbstractRpcServer|\PHPUnit_Framework_MockObject_MockObject $server
     */
    public function createServerMock($dispatcher, $serializer, array $methods = [])
    {
        $methods = array_unique(array_merge($methods, ['createRequest', 'createResponse', 'prepareResponseData', 'prepareErrorResponseData']));
        /** @var AbstractRpcServer|\PHPUnit_Framework_MockObject_MockObject $server */
        return $this->getMockBuilder('Moriony\RpcServer\Server\AbstractRpcServer')
            ->setConstructorArgs([$dispatcher, $serializer])
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcher
     */
    public function createEventDispatcherMock(array $methods = [])
    {
        return $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JsonRpcRequest
     */
    public function createRpcRequestMock(array $methods = [])
    {
        return $this->getMockBuilder('Moriony\RpcServer\Request\JsonRpcRequest')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
