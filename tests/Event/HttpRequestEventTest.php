<?php

namespace Moriony\RpcServer\Math;

use Moriony\RpcServer\Event\HttpRequestEvent;
use Symfony\Component\HttpFoundation\Request;

class HttpRequestEventTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $event = new HttpRequestEvent(new Request());
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', $event);
    }

    public function testGetRequest()
    {
        $request = new Request();
        $event = new HttpRequestEvent($request);
        $this->assertSame($request, $event->getRequest());
    }
}