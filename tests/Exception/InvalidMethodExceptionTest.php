<?php

namespace Moriony\RpcServer\Math;

use Moriony\RpcServer\Exception\InvalidMethodException;

class InvalidMethodExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $exception = new InvalidMethodException();
        $this->assertInstanceOf('Moriony\RpcServer\Exception\RpcException', $exception);
    }

    public function testGetCode()
    {
        $exception = new InvalidMethodException();
        $this->assertSame(-32601, $exception->getCode());
    }
}