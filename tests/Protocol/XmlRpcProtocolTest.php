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
