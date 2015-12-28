<?php

namespace Moriony\RpcServer\Request;

use Symfony\Component\HttpFoundation\Request;

class XmlRpcRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementationOf()
    {
        $requestMock = $this->getMockBuilder('Moriony\RpcServer\Request\XmlRpcRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Moriony\RpcServer\Request\RpcRequestInterface', $requestMock);
    }

    public function testConstructor()
    {
        $httpRequest = new Request();
        $request = new XmlRpcRequest(
            $httpRequest,
            'test_method',
            ['test_param' => 'test_param_value']
        );

        $this->assertSame($httpRequest, $request->getHttpRequest());
        $this->assertSame('test_method', $request->getMethod());
        $this->assertSame(['test_param' => 'test_param_value'], $request->getParams());
    }

    public function testGetExistsParam()
    {
        $request = new XmlRpcRequest(new Request(), '', ['test_param' => 'test_param_value']);
        $this->assertSame('test_param_value', $request->get('test_param'));
    }

    public function testGetNotExistsParam()
    {
        $request = new XmlRpcRequest(new Request(), '', ['test_param' => 'test_param_value']);
        $this->assertSame(null, $request->get('not_exists_param'));
    }


    public function testGetNotExistsExtraData()
    {
        $request = new XmlRpcRequest(new Request(), '', []);
        $this->assertNull($request->getExtraData('test_extra'));
    }

    public function testGetExistsExtraData()
    {
        $request = new XmlRpcRequest(new Request(), '', []);
        $request->setExtraData('test_extra', 'test_extra_value');
        $this->assertSame('test_extra_value', $request->getExtraData('test_extra'));
    }

    public function testSetExistsExtraData()
    {
        $request = new XmlRpcRequest(new Request(), '', []);
        $request->setExtraData('test_extra', 'test_extra_value_1');
        $request->setExtraData('test_extra', 'test_extra_value_2');
        $this->assertSame('test_extra_value_2', $request->getExtraData('test_extra'));
    }

    public function testHasExistsExtraData()
    {
        $request = new XmlRpcRequest(new Request(), '', []);
        $request->setExtraData('test_extra', 'test_extra_value');
        $this->assertTrue($request->hasExtraData('test_extra'));
    }

    public function testHasNotExistsExtraData()
    {
        $request = new XmlRpcRequest(new Request(), '', []);
        $this->assertFalse($request->hasExtraData('text_extra'));
    }
}