<?php

namespace Moriony\RpcServer\Handler;

use Moriony\RpcServer\Request\JsonRpcRequest;
use Symfony\Component\DependencyInjection\Container;

class ServiceContainerHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testUndefinedService()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidMethodException', 'Requested method does not exist.', -32601);
        $request = $this->createRpcRequestMock();
        $container = new Container();
        $handler = new ServiceContainerHandler($container, 'test', 'test');
        $handler->handle($request);
    }

    public function testUndefinedServiceMethod()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidMethodException', 'Requested method does not exist.', -32601);
        $request = $this->createRpcRequestMock();
        $container = new Container();
        $container->set('test', $request);
        $handler = new ServiceContainerHandler($container, 'test', 'test');
        $handler->handle($request);
    }

    public function testSuccessfullCall()
    {
        $request = $this->createRpcRequestMock(['test']);
        $request->expects($this->once())
            ->method('test')
            ->with($request)
            ->willReturn('test_result');

        $container = new Container();
        $container->set('test', $request);
        $handler = new ServiceContainerHandler($container, 'test', 'test');
        $this->assertSame('test_result', $handler->handle($request));
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
