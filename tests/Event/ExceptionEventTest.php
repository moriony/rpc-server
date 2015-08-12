<?php

namespace Moriony\RpcServer\Math;

use Moriony\RpcServer\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Request;

class ExceptionEventTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $event = new ExceptionEvent(new Request(), new \Exception());
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', $event);
    }

    public function testGetRequest()
    {
        $request = new Request();
        $event = new ExceptionEvent($request, new \Exception());
        $this->assertSame($request, $event->getRequest());
    }

    public function testGetException()
    {
        $exception = new \Exception();
        $event = new ExceptionEvent(new Request(), $exception);
        $this->assertSame($exception, $event->getException());
    }
}