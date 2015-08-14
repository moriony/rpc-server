<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Request\JsonRpcRequest;
use Moriony\RpcServer\Response\JsonRpcResponse;
use Moriony\RpcServer\Server\JsonRpcServer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcServerTest extends \PHPUnit_Framework_TestCase
{
    protected $server;

    public function setUp()
    {
        $this->server = new JsonRpcServer($this->createEventDispatcherMock(), $this->createSerializerMock());
    }

    public function testCreateRequest()
    {
        $method = $this->setMethodAccessible($this->server, 'createRequest');
        $request = $this->createHttpRequest();
        $rpcRequest = $method->invoke($this->server, $request);

        $this->assertInstanceOf('Moriony\RpcServer\Request\JsonRpcRequest', $rpcRequest);
        $this->assertSame($request, $rpcRequest->getHttpRequest());
    }

    public function testCreateResponse()
    {
        $method = $this->setMethodAccessible($this->server, 'createResponse');

        /** @var JsonRpcResponse $rpcResponse */
        $rpcResponse = $method->invoke($this->server, 'test_content');

        $this->assertInstanceOf('Moriony\RpcServer\Response\JsonRpcResponse', $rpcResponse);
        $this->assertSame(200, $rpcResponse->getStatusCode());
        $this->assertSame('application/json', $rpcResponse->headers->get('Content-Type'));
        $this->assertSame('test_content', $rpcResponse->getContent());
    }

    public function testPrepareResponseData()
    {
        $method = $this->setMethodAccessible($this->server, 'prepareResponseData');
        $request = $this->createRpcRequestMock(['getId']);
        $request->method('getId')->willReturn('test_id');

        $responseData = $method->invoke($this->server, $request, 'test_result');
        $expected = [
            'jsonrpc' => '2.0',
            'result' => 'test_result',
            'id' => 'test_id'
        ];
        $this->assertEquals($expected, $responseData);
    }

    /**
     * @dataProvider provideErrorResponseData
     */
    public function testPrepareErrorResponseData($exception, $expected)
    {
        $method = $this->setMethodAccessible($this->server, 'prepareErrorResponseData');
        $responseData = $method->invoke($this->server, $exception);
        $this->assertEquals($expected, $responseData);
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SerializerInterface
     */
    public function createSerializerMock()
    {
        return $this->getMockBuilder('Moriony\RpcServer\ResponseSerializer\SerializerInterface')
            ->getMock();
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcher
     */
    public function createEventDispatcherMock(array $methods = [])
    {
        return $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->setMethods($methods)
            ->getMock();
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JsonRpcRequest
     */
    public function createRpcRequestMock(array $methods = [])
    {
        return $this->getMockBuilder('Moriony\RpcServer\Request\JsonRpcRequest')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    public function createHttpRequest()
    {
        $content = json_encode([
            'jsonrpc' => "2.0",
            'service' => 'serviceName',
            'method' => 'methodName',
            'params' => [],
            'id' => 'test',
        ]);
        $request = new Request([], [], [], [], [], [], $content);
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/json');
        return $request;
    }

    public function setMethodAccessible($object, $method)
    {
        $object = new \ReflectionObject($object);
        $method = $object->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function provideErrorResponseData()
    {
        return [
            // Case 1
            [
                new \Exception('test message', 123),
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32603,
                        'message' => 'Unexpected error occurred.',
                        'data' => null
                    ],
                    'id' => null
                ]
            ],
            // Case 2
            [
                new InvalidMethodException('test message'),
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32601,
                        'message' => 'test message',
                        'data' => null
                    ],
                    'id' => null
                ]
            ],
        ];
    }
}
