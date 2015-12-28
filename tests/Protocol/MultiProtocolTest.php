<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Exception\RpcException;
use Moriony\RpcServer\Protocol\MultiProtocol;
use Symfony\Component\HttpFoundation\Request;

class MultiProtocolTest extends \PHPUnit_Framework_TestCase
{
    /** @var MultiProtocol */
    protected $protocol;

    public function setUp()
    {
        $this->protocol = new MultiProtocol([
            $this->createInvalidProtocol(),
            $this->createValidProtocol(),
        ]);
    }

    public function testConstructor()
    {
        $protocols = [
            $this->createValidProtocol(),
        ];
        $protocol = new MultiProtocol($protocols);
        $this->assertSame($protocols, $protocol->getProtocols());
    }

    public function testGetName()
    {
        $this->assertSame('invalid, valid', $this->protocol->getName());
    }

    public function testSetProtocols()
    {
        $protocols = [
            $this->createValidProtocol(),
        ];
        $this->protocol->setProtocols($protocols);
        $this->assertSame($protocols, $this->protocol->getProtocols());
    }

    public function testAddProtocol()
    {
        $protocol = $this->createValidProtocol();
        $this->protocol->addProtocol($protocol);
        $protocols = $this->protocol->getProtocols();

        $this->assertCount(3, $protocols);
        $this->assertSame($protocol, end($protocols));
    }

    public function testGetSupportedProtocolException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\RequestParseException', 'Protocol not detected.');
        $this->protocol->getSupportedProtocol();
    }

    public function testGetSupportedProtocol()
    {
        $this->protocol->createRequest(new Request());
        $this->assertSame('valid', $this->protocol->getSupportedProtocol()->getName());
    }

    public function testCreateRequest()
    {
        $request = $this->protocol->createRequest(new Request());
        $this->assertSame('test', $request->getMethod());
    }

    public function testCreateRequestException()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\RequestParseException', 'Protocol not supported. Available protocols: invalid');
        $protocol = new MultiProtocol([
            $this->createInvalidProtocol(),
        ]);
        $protocol->createRequest(new Request());
    }

    public function testCreateResponse()
    {
        $request = $this->protocol->createRequest(new Request());
        $response = $this->protocol->createResponse($request, 'test');
        $this->assertSame('test_content', $response->getContent());
    }

    public function testCreateErrorResponse()
    {
        $this->protocol->createRequest(new Request());
        $response = $this->protocol->createErrorResponse(new \Exception());
        $this->assertSame('test_content', $response->getContent());
    }

    public function createInvalidProtocol()
    {
        $mock = $this->getMockBuilder('Moriony\RpcServer\Protocol\ProtocolInterface')
            ->setMethods(['createRequest', 'createResponse', 'createErrorResponse', 'getName'])
            ->getMock();

        $mock->method('getName')
            ->willReturn('invalid');

        $mock->method('createRequest')
            ->willThrowException(new RpcException('test_exception'));

        return $mock;
    }

    public function createValidProtocol()
    {
        $request = $this->getMockBuilder('Moriony\RpcServer\Request\RpcRequestInterface')
            ->getMock();

        $request->method('getMethod')
            ->willReturn('test');

        $response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->getMock();

        $response->method('getContent')
            ->willReturn('test_content');

        $mock = $this->getMockBuilder('Moriony\RpcServer\Protocol\ProtocolInterface')
            ->setMethods(['createRequest', 'createResponse', 'createErrorResponse', 'getName'])
            ->getMock();

        $mock->method('getName')
            ->willReturn('valid');

        $mock->method('createRequest')
            ->willReturn($request);

        $mock->method('createResponse')
            ->willReturn($response);

        $mock->method('createErrorResponse')
            ->willReturn($response);

        return $mock;
    }
}
