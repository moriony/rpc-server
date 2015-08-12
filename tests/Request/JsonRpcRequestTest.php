<?php

namespace Moriony\RpcServer\Math;

use Moriony\RpcServer\Request\JsonRpcRequest;
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

    public function testInvalidHttpMethodException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid HTTP method, method should be POST.', -32600);
        new JsonRpcRequest(new Request());
    }

    public function testInvalidContentTypeException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Content-Type should be application/json.', -32600);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        new JsonRpcRequest($request);
    }

    public function testInvalidBodyException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\RequestParseException', 'Invalid request body, should be valid json.', -32700);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        new JsonRpcRequest($request);
    }

    public function testJsonRpcRequiredException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, should include jsonrpc.', -32600);
        $request = new Request(array(), array(), array(), array(), array(), array(), json_encode(['id' => 1]));
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        new JsonRpcRequest($request);
    }

    public function testIdRequiredException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, should include id.', -32600);
        $request = new Request(array(), array(), array(), array(), array(), array(), json_encode(['jsonrpc' => '2.0']));
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        new JsonRpcRequest($request);
    }

    public function testMethodRequiredException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, should include method.', -32600);
        $content = json_encode([
            'jsonrpc' => '2.0',
            'id' => 'test'
        ]);
        $request = new Request(array(), array(), array(), array(), array(), array(), $content);
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        new JsonRpcRequest($request);
    }

    public function testParamsRequiredException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, should include params.', -32600);
        $content = json_encode([
            'jsonrpc' => '2.0',
            'id' => 'test',
            'method' => 'test'
        ]);
        $request = new Request(array(), array(), array(), array(), array(), array(), $content);
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        new JsonRpcRequest($request);
    }

    public function testInvalidParamsTypeException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, params should be an array or object.', -32600);
        $content = json_encode([
            'jsonrpc' => '2.0',
            'id' => 'test',
            'method' => 'test',
            'params' => 'test'
        ]);
        $request = new Request(array(), array(), array(), array(), array(), array(), $content);
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        new JsonRpcRequest($request);
    }

    public function testGetHttpRequest()
    {
        $httpRequest = $this->createHttpRequest();
        $request = new JsonRpcRequest($httpRequest);
        $this->assertSame($httpRequest, $request->getHttpRequest());
    }

    public function testGetData()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $expected = [
            'jsonrpc' => '2.0',
            'id' => 'test_id',
            'method' => 'test_method',
            'params' => [
                'test_param' => 1
            ]
        ];
        $this->assertEquals($expected, $request->getData());
    }

    public function testGetExistsParam()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $this->assertSame(1, $request->get('test_param'));
    }

    public function testGetNotExistsParam()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $this->assertSame(null, $request->get('not_exists_param'));
    }

    public function testGetId()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $this->assertSame('test_id', $request->getId());
    }

    public function testSetId()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $request->setId('new id');
        $this->assertSame('new id', $request->getId());
    }

    public function testGetProtocolVersion()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $this->assertSame('2.0', $request->getProtocolVersion());
    }

    public function testSetProtocolVersion()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $request->setProtocolVersion('new protocol version');
        $this->assertSame('new protocol version', $request->getProtocolVersion());
    }

    public function testGetMethod()
    {
        $httpRequest = $this->createHttpRequest();
        $request = new JsonRpcRequest($httpRequest);
        $this->assertSame('test_method', $request->getMethod());
    }

    public function testSetMethod()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $request->setMethod('new method');
        $this->assertSame('new method', $request->getMethod());
    }

    public function testGetParams()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $expected = [
            'test_param' => 1
        ];
        $this->assertEquals($expected, $request->getParams());
    }

    public function testGetNotExistsExtraData()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $this->assertNull($request->getExtraData('test_extra'));
    }

    public function testGetExistsExtraData()
    {
        $request = new JsonRpcRequest($this->createHttpRequest(), [
            'test_extra' => 1
        ]);
        $this->assertSame(1, $request->getExtraData('test_extra'));
    }

    public function testSetExistsExtraData()
    {
        $request = new JsonRpcRequest($this->createHttpRequest(), [
            'test_extra' => 1
        ]);
        $request->setExtraData('test_extra', 'new extra data');
        $this->assertSame('new extra data', $request->getExtraData('test_extra'));
    }

    public function testHasExistsExtraData()
    {
        $request = new JsonRpcRequest($this->createHttpRequest(), [
            'test_extra' => 1
        ]);
        $this->assertTrue($request->hasExtraData('test_extra'));
    }

    public function testHasNotExistsExtraData()
    {
        $request = new JsonRpcRequest($this->createHttpRequest());
        $this->assertFalse($request->hasExtraData('text_extra'));
    }

    protected function createHttpRequest()
    {
        $content = json_encode([
            'jsonrpc' => '2.0',
            'id' => 'test_id',
            'method' => 'test_method',
            'params' => [
                'test_param' => 1
            ]
        ]);
        $request = new Request(array(), array(), array(), array(), array(), array(), $content);
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        return $request;
    }
}