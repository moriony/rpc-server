<?php

namespace Moriony\RpcServer\Event;

use Moriony\RpcServer\Request\RpcRequestInterface;

class ServiceEventTest extends \PHPUnit_Framework_TestCase
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
        $event = new ServiceEvent($this->createRequestMock(), 'method');
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', $event);
    }

    public function testGetRequest()
    {
        $request = $this->createRequestMock();
        $event = new ServiceEvent($request, 'test');
        $this->assertSame($request, $event->getRequest());
    }

    public function testGetServiceMethod()
    {
        $event = new ServiceEvent($this->createRequestMock(), 'test');
        $this->assertSame('test', $event->getServiceMethod());
    }
}