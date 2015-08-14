<?php

namespace Moriony\RpcServer\Handler;

use Moriony\RpcServer\Request\JsonRpcRequest;

class ClosureHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEventDispatcher()
    {
        $request = $this->createRpcRequestMock();
        $called = false;
        $handler = new ClosureHandler(function ($req) use(&$called, $request) {
            $called = true;
            $this->assertSame($request, $req);
        });

        $handler->handle($request);
        if (!$called) {
            $this->fail('Closure must be called.');
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JsonRpcRequest
     */
    public function createRpcRequestMock()
    {
        return $this->getMockBuilder('Moriony\RpcServer\Request\JsonRpcRequest')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
