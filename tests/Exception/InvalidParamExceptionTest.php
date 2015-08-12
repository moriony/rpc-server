<?php

namespace Moriony\RpcServer\Math;

use Moriony\RpcServer\Exception\InvalidParamException;

class InvalidParamExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $exception = new InvalidParamException();
        $this->assertInstanceOf('Moriony\RpcServer\Exception\RpcException', $exception);
    }

    public function testGetCode()
    {
        $exception = new InvalidParamException();
        $this->assertSame(-32602, $exception->getCode());
    }
}