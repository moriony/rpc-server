<?php

namespace Moriony\RpcServer\Event;

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