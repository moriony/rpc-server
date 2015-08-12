<?php

namespace Moriony\RpcServer\Math;

use Moriony\RpcServer\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class ResponseEventTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $event = new ResponseEvent(new Response());
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', $event);
    }

    public function testGetResponse()
    {
        $response = new Response();
        $event = new ResponseEvent($response);
        $this->assertSame($response, $event->getResponse());
    }
}