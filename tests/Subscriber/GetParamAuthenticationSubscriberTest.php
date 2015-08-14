<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Event\HttpRequestEvent;
use Moriony\RpcServer\Subscriber\GetParamAuthenticationSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class GetParamAuthenticationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $expected = [
            'rpc_server.http_request' => [['authentication']]
        ];
        $this->assertEquals($expected, GetParamAuthenticationSubscriber::getSubscribedEvents());
    }

    public function testAuthenticationFail()
    {
        $this->setExpectedException('Moriony\RpcServer\Exception\InvalidRequestException', 'Authentication failed.', -32600);
        $subscriber = new GetParamAuthenticationSubscriber('private_key');
        $request = new Request();
        $subscriber->authentication(new HttpRequestEvent($request));
    }

    public function testAuthenticationSuccess()
    {
        $subscriber = new GetParamAuthenticationSubscriber('private_key', 'key');
        $request = new Request(['key' => 'private_key']);
        $subscriber->authentication(new HttpRequestEvent($request));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    public function createLoggerMock()
    {
        return $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log'])
            ->getMock();
    }
}
