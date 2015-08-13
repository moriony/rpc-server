<?php

namespace Moriony\RpcServer\Exception;

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