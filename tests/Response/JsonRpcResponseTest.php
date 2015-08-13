<?php

namespace Moriony\RpcServer\Response;

class JsonRpcResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', new JsonRpcResponse());
    }

    public function testContentType()
    {
        $response = new JsonRpcResponse();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

}