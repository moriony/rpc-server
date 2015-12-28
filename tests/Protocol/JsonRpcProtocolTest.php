<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Protocol\JsonRpcProtocol;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcProtocolTest extends \PHPUnit_Framework_TestCase
{
    /** @var  JsonRpcProtocol */
    protected $protocol;

    public function setUp()
    {
        $this->protocol = new JsonRpcProtocol(new NativeJsonSerializer());
    }

    public function testGetName()
    {
        $this->assertSame('JSON-RPC 2.0', $this->protocol->getName());
    }

    public function testInvalidHttpMethodException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid HTTP method, method should be POST.', -32600);
        $this->protocol->createRequest(new Request());
    }

    public function testInvalidContentTypeException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Content-Type should be application/json.', -32600);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $this->protocol->createRequest($request);
    }

    public function testInvalidBodyException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\RequestParseException', 'Invalid request body, should be valid json.', -32700);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        $this->protocol->createRequest($request);
    }

    public function testJsonRpcRequiredException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, should include jsonrpc.', -32600);
        $request = new Request(array(), array(), array(), array(), array(), array(), json_encode(['id' => 1]));
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        $this->protocol->createRequest($request);
    }

    public function testIdRequiredException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Invalid request body, should include id.', -32600);
        $request = new Request(array(), array(), array(), array(), array(), array(), json_encode(['jsonrpc' => '2.0']));
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('CONTENT_TYPE', 'application/json');
        $this->protocol->createRequest($request);
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
        $this->protocol->createRequest($request);
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
        $this->protocol->createRequest($request);
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
        $this->protocol->createRequest($request);
    }


    public function testCreateRequest()
    {
        $request = $this->createHttpRequest();
        $rpcRequest = $this->protocol->createRequest($request);;

        $this->assertInstanceOf('Moriony\RpcServer\Request\JsonRpcRequest', $rpcRequest);
        $this->assertSame($request, $rpcRequest->getHttpRequest());
    }

    public function testCreateResponse()
    {
        $request = $this->createHttpRequest();
        $rpcRequest = $this->protocol->createRequest($request);
        $rpcResponse = $this->protocol->createResponse($rpcRequest, [
            'test' => 'test'
        ]);

        $this->assertInstanceOf('Moriony\RpcServer\Response\JsonRpcResponse', $rpcResponse);
        $this->assertSame(200, $rpcResponse->getStatusCode());
        $this->assertSame('application/json', $rpcResponse->headers->get('Content-Type'));
        $this->assertSame('{"jsonrpc":"2.0","result":{"test":"test"},"id":"test"}', $rpcResponse->getContent());
    }

    /**
     * @dataProvider provideErrorResponseData
     */
    public function testCreateErrorResponse($exception, $expectedContent)
    {
        $rpcResponse = $this->protocol->createErrorResponse($exception);

        $this->assertInstanceOf('Moriony\RpcServer\Response\JsonRpcResponse', $rpcResponse);
        $this->assertSame(200, $rpcResponse->getStatusCode());
        $this->assertSame('application/json', $rpcResponse->headers->get('Content-Type'));
        $this->assertSame($expectedContent, $rpcResponse->getContent());
    }

    public function createHttpRequest()
    {
        $content = json_encode([
            'jsonrpc' => "2.0",
            'method' => 'methodName',
            'params' => [],
            'id' => 'test',
        ]);
        $request = new Request([], [], [], [], [], [], $content);
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/json');
        return $request;
    }

    public function provideErrorResponseData()
    {
        return [
            // Case 1
            [
                new \Exception('test message', 123),
                '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Unexpected error occurred.","data":null},"id":null}',
            ],
            // Case 2
            [
                new InvalidMethodException('test message'),
                '{"jsonrpc":"2.0","error":{"code":-32601,"message":"test message","data":null},"id":null}',
            ],
        ];
    }
}
