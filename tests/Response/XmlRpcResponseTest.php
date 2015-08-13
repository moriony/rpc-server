<?php

namespace Moriony\RpcServer\Response;

class XmlRpcResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', new XmlRpcResponse());
    }

    public function testContentType()
    {
        $response = new XmlRpcResponse();
        $this->assertEquals('application/xml', $response->headers->get('Content-Type'));
    }
}
