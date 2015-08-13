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

    public function testInvalidHttpMethodException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid HTTP method, method should be POST.', -32600);
        new XmlRpcRequest(new Request());
    }

    public function testInvalidContentTypeException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Content-Type should be application/xml.', -32600);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        new XmlRpcRequest($request);
    }

    public function testInvalidBodyException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\RequestParseException', 'Invalid request body, should be valid xml.', -32700);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/xml');
        new XmlRpcRequest($request);
    }

    public function testMethodRequiredException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, should include method.', -32600);

        $content = '<?xml version="1.0" encoding="UTF-8"?>
        <methodCall>
          <methodName></methodName>
          <params></params>
        </methodCall>
        ';
        $request = new Request(array(), array(), array(), array(), array(), array(), $content);
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/xml');
        new XmlRpcRequest($request);
    }

    public function testInvalidParamsTypeException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, params should be an array or object.', -32600);
        $content = '<?xml version="1.0" encoding="UTF-8"?>
        <methodCall>
          <methodName>test_method</methodName>
        </methodCall>
        ';
        $request = new Request(array(), array(), array(), array(), array(), array(), $content);
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/xml');
        new XmlRpcRequest($request);
    }

    public function testGetHttpRequest()
    {
        $httpRequest = $this->createHttpRequest();
        $request = new XmlRpcRequest($httpRequest);
        $this->assertSame($httpRequest, $request->getHttpRequest());
    }

    public function testGetExistsParam()
    {
        $request = new XmlRpcRequest($this->createHttpRequest());
        $this->assertSame('test_param', $request->get(0));
    }

    public function testGetNotExistsParam()
    {
        $request = new XmlRpcRequest($this->createHttpRequest());
        $this->assertSame(null, $request->get('not_exists_param'));
    }

    public function testGetMethod()
    {
        $httpRequest = $this->createHttpRequest();
        $request = new XmlRpcRequest($httpRequest);
        $this->assertSame('test_method', $request->getMethod());
    }

    public function testSetMethod()
    {
        $request = new XmlRpcRequest($this->createHttpRequest());
        $request->setMethod('new method');
        $this->assertSame('new method', $request->getMethod());
    }

    public function testGetParams()
    {
        $request = new XmlRpcRequest($this->createHttpRequest());
        $this->assertEquals([ 'test_param' ], $request->getParams());
    }

    public function testGetNotExistsExtraData()
    {
        $request = new XmlRpcRequest($this->createHttpRequest());
        $this->assertNull($request->getExtraData('test_extra'));
    }

    public function testGetExistsExtraData()
    {
        $request = new XmlRpcRequest($this->createHttpRequest(), [
            'test_extra' => 1
        ]);
        $this->assertSame(1, $request->getExtraData('test_extra'));
    }

    public function testSetExistsExtraData()
    {
        $request = new XmlRpcRequest($this->createHttpRequest(), [
            'test_extra' => 1
        ]);
        $request->setExtraData('test_extra', 'new extra data');
        $this->assertSame('new extra data', $request->getExtraData('test_extra'));
    }

    public function testHasExistsExtraData()
    {
        $request = new XmlRpcRequest($this->createHttpRequest(), [
            'test_extra' => 1
        ]);
        $this->assertTrue($request->hasExtraData('test_extra'));
    }

    public function testHasNotExistsExtraData()
    {
        $request = new XmlRpcRequest($this->createHttpRequest());
        $this->assertFalse($request->hasExtraData('text_extra'));
    }

    protected function createHttpRequest()
    {
        $content = xmlrpc_encode_request('test_method', array('test_param'));
        $request = new Request(array(), array(), array(), array(), array(), array(), $content);
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/xml');
        return $request;
    }
}