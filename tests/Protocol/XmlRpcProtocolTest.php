<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Protocol\XmlRpcProtocol;
use Symfony\Component\HttpFoundation\Request;

class XmlRpcProtocolTest extends \PHPUnit_Framework_TestCase
{
    /** @var XmlRpcProtocol */
    protected $protocol;

    public function setUp()
    {
        $this->protocol = new XmlRpcProtocol(new NativeJsonSerializer());
    }

    public function testGetName()
    {
        $this->assertSame('XML-RPC', $this->protocol->getName());
    }

    public function testInvalidHttpMethodException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid HTTP method, method should be POST.', -32600);
        $this->protocol->createRequest(new Request());
    }

    public function testInvalidContentTypeException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Content-Type should be application/xml.', -32600);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $this->protocol->createRequest($request);
    }

    public function testInvalidBodyException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\RequestParseException', 'Invalid request body, should be valid xml.', -32700);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/xml');
        $this->protocol->createRequest($request);
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
        $this->protocol->createRequest($request);
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
        $this->protocol->createRequest($request);
    }

    public function testCreateRequest()
    {
        $request = $this->createHttpRequest();
        $rpcRequest = $this->protocol->createRequest($request);

        $this->assertInstanceOf('Moriony\RpcServer\Request\XmlRpcRequest', $rpcRequest);
        $this->assertSame($request, $rpcRequest->getHttpRequest());
    }

    public function testCreateResponse()
    {
        $request = $this->createHttpRequest();
        $rpcRequest = $this->protocol->createRequest($request);
        $rpcResponse = $this->protocol->createResponse($rpcRequest, [
            'test' => 'test'
        ]);

        $this->assertInstanceOf('Moriony\RpcServer\Response\XmlRpcResponse', $rpcResponse);
        $this->assertSame(200, $rpcResponse->getStatusCode());
        $this->assertSame('application/xml', $rpcResponse->headers->get('Content-Type'));
        $this->assertSame('{"test":"test"}', $rpcResponse->getContent());
    }

    /**
     * @dataProvider provideErrorResponseData
     */
    public function testCreateErrorResponse($exception, $expectedContent)
    {
        $rpcResponse = $this->protocol->createErrorResponse($exception);

        $this->assertInstanceOf('Moriony\RpcServer\Response\XmlRpcResponse', $rpcResponse);
        $this->assertSame(200, $rpcResponse->getStatusCode());
        $this->assertSame('application/xml', $rpcResponse->headers->get('Content-Type'));
        $this->assertSame($expectedContent, $rpcResponse->getContent());
    }

    public function createHttpRequest()
    {
        $content = xmlrpc_encode_request('methodName', []);
        $request = new Request([], [], [], [], [], [], $content);
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/xml');
        return $request;
    }

    public function provideErrorResponseData()
    {
        return [
            // Case 1
            [
                new \Exception('test message', 123),
                '{"faultCode":-32603,"faultString":"Unexpected error occurred."}',
            ],
            // Case 2
            [
                new InvalidMethodException('test message'),
                '{"faultCode":-32601,"faultString":"test message"}',
            ],
        ];
    }
}
