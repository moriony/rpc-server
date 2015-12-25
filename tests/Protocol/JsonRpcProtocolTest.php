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

    public function testCreateRequest()
    {
        $request = $this->createHttpRequest();
        $rpcRequest = $this->protocol->createRequest($request);

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
