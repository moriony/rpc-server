<?php

namespace Moriony\RpcServer\Math;

use Moriony\RpcServer\Event\RpcRequestEvent;
use Moriony\RpcServer\Request\RpcRequestInterface;

class RpcRequestEventTest extends \PHPUnit_Framework_TestCase
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
        $event = new RpcRequestEvent($this->createRequestMock());
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', $event);
    }

    public function testGetRequest()
    {
        $request = $this->createRequestMock();
        $event = new RpcRequestEvent($request);
        $this->assertSame($request, $event->getRequest());
    }
}