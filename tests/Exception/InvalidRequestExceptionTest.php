<?php

namespace Moriony\RpcServer\Exception;

class InvalidRequestExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $exception = new InvalidRequestException();
        $this->assertInstanceOf('Moriony\RpcServer\Exception\RpcException', $exception);
    }

    public function testGetCode()
    {
        $exception = new InvalidRequestException();
        $this->assertSame(-32600, $exception->getCode());
    }
}