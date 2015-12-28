<?php

namespace Moriony\RpcServer\Request;

use Symfony\Component\HttpFoundation\Request;

class JsonRpcRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementationOf()
    {
        $requestMock = $this->getMockBuilder('Moriony\RpcServer\Request\JsonRpcRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Moriony\RpcServer\Request\RpcRequestInterface', $requestMock);
    }

    public function testConstructor()
    {
        $httpRequest = new Request();
        $request = new JsonRpcRequest(
            $httpRequest,
            '2.0',
            'test_id',
            'test_method',
            ['test_param' => 'test_param_value']
        );

        $this->assertSame($httpRequest, $request->getHttpRequest());
        $this->assertSame('2.0', $request->getProtocolVersion());
        $this->assertSame('test_id', $request->getId());
        $this->assertSame('test_method', $request->getMethod());
        $this->assertSame(['test_param' => 'test_param_value'], $request->getParams());
    }

    public function testGetExistsParam()
    {
        $request = new JsonRpcRequest(new Request(), '', '', '', ['test_param' => 'test_param_value']);
        $this->assertSame('test_param_value', $request->get('test_param'));
    }

    public function testGetNotExistsParam()
    {
        $request = new JsonRpcRequest(new Request(), '', '', '', ['test_param' => 'test_param_value']);
        $this->assertSame(null, $request->get('not_exists_param'));
    }


    public function testGetNotExistsExtraData()
    {
        $request = new JsonRpcRequest(new Request(), '', '', '', []);
        $this->assertNull($request->getExtraData('test_extra'));
    }

    public function testGetExistsExtraData()
    {
        $request = new JsonRpcRequest(new Request(), '', '', '', []);
        $request->setExtraData('test_extra', 'test_extra_value');
        $this->assertSame('test_extra_value', $request->getExtraData('test_extra'));
    }

    public function testSetExistsExtraData()
    {
        $request = new JsonRpcRequest(new Request(), '', '', '', []);
        $request->setExtraData('test_extra', 'test_extra_value_1');
        $request->setExtraData('test_extra', 'test_extra_value_2');
        $this->assertSame('test_extra_value_2', $request->getExtraData('test_extra'));
    }

    public function testHasExistsExtraData()
    {
        $request = new JsonRpcRequest(new Request(), '', '', '', []);
        $request->setExtraData('test_extra', 'test_extra_value');
        $this->assertTrue($request->hasExtraData('test_extra'));
    }

    public function testHasNotExistsExtraData()
    {
        $request = new JsonRpcRequest(new Request(), '', '', '', []);
        $this->assertFalse($request->hasExtraData('text_extra'));
    }
}