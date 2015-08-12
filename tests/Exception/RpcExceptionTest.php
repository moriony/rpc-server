<?php

namespace Moriony\RpcServer\Math;

use Moriony\RpcServer\Exception\RpcException;

class RpcExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $exception = new RpcException();
        $this->assertInstanceOf('\Exception', $exception);
    }

    public function testImplementationOf()
    {
        $exception = new RpcException();
        $this->assertInstanceOf('Moriony\RpcServer\Exception\RpcExceptionInterface', $exception);
    }

    public function testGetCode()
    {
        $exception = new RpcException();
        $this->assertSame(-32603, $exception->getCode());
    }

    public function testGetData()
    {
        $exception = new RpcException();
        $exception->setData('test');
        $this->assertSame('test', $exception->getData());
    }
}