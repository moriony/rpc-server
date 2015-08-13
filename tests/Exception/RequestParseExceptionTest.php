<?php

namespace Moriony\RpcServer\Exception;

class RequestParseExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $exception = new RequestParseException();
        $this->assertInstanceOf('Moriony\RpcServer\Exception\RpcException', $exception);
    }

    public function testGetCode()
    {
        $exception = new RequestParseException();
        $this->assertSame(-32700, $exception->getCode());
    }
}