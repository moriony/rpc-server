<?php

namespace Moriony\RpcServer\Event;

use Moriony\RpcServer\Request\RpcRequestInterface;

class MethodCallEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RpcRequestInterface
     */
    protected function createRequestMock()
    {
        return $this->getMockBuilder('Moriony\RpcServer\Request\RpcRequestInterface')
            ->getMock();
    }

    public function testInstanceOf()
    {
        $event = new MethodCallEvent($this->createRequestMock(), 'test');
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', $event);
    }

    public function testGetRequest()
    {
        $request = $this->createRequestMock();
        $event = new MethodCallEvent($request, 'test');
        $this->assertSame($request, $event->getRequest());
    }

    public function testGetResult()
    {
        $event = new MethodCallEvent($this->createRequestMock(), 'test');
        $this->assertSame('test', $event->getResult());
    }
}