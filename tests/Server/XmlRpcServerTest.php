<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Exception\InvalidMethodException;
use Moriony\RpcServer\Request\XmlRpcRequest;
use Moriony\RpcServer\Response\XmlRpcResponse;
use Moriony\RpcServer\Server\XmlRpcServer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

class XmlRpcServerTest extends \PHPUnit_Framework_TestCase
{
    protected $server;

    public function setUp()
    {
        $this->server = new XmlRpcServer($this->createEventDispatcherMock(), $this->createSerializerMock());
    }

    public function testCreateRequest()
    {
        $method = $this->setMethodAccessible($this->server, 'createRequest');
        $request = $this->createHttpRequest();
        $rpcRequest = $method->invoke($this->server, $request);

        $this->assertInstanceOf('Moriony\RpcServer\Request\XmlRpcRequest', $rpcRequest);
        $this->assertSame($request, $rpcRequest->getHttpRequest());
    }

    public function testCreateResponse()
    {
        $method = $this->setMethodAccessible($this->server, 'createResponse');

        /** @var XmlRpcResponse $rpcResponse */
        $rpcResponse = $method->invoke($this->server, 'test_content');

        $this->assertInstanceOf('Moriony\RpcServer\Response\XmlRpcResponse', $rpcResponse);
        $this->assertSame(200, $rpcResponse->getStatusCode());
        $this->assertSame('application/xml', $rpcResponse->headers->get('Content-Type'));
        $this->assertSame('test_content', $rpcResponse->getContent());
    }

    public function testPrepareResponseData()
    {
        $method = $this->setMethodAccessible($this->server, 'prepareResponseData');
        $request = $this->createRpcRequestMock();
        $responseData = $method->invoke($this->server, $request, 'test_data');
        $this->assertEquals('test_data', $responseData);
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
     * @return \PHPUnit_Framework_MockObject_MockObject|XmlRpcRequest
     */
    public function createRpcRequestMock(array $methods = [])
    {
        return $this->getMockBuilder('Moriony\RpcServer\Request\XmlRpcRequest')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    public function createHttpRequest()
    {
        $content = xmlrpc_encode_request('methodName', []);
        $request = new Request([], [], [], [], [], [], $content);
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/xml');
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
                    'faultCode' => -32603,
                    'faultString' => 'Unexpected error occurred.',
                ]
            ],
            // Case 2
            [
                new InvalidMethodException('test message'),
                [
                    'faultCode' => -32601,
                    'faultString' => 'test message',
                ]
            ],
        ];
    }
}
