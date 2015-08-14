<?php

namespace Moriony\RpcServer\ResponseSerializer;

use Moriony\RpcServer\Event\ExceptionEvent;
use Moriony\RpcServer\Subscriber\ExceptionLoggerSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class ExceptionLoggerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $expected = [
            'rpc_server.exception' => [['onException']]
        ];
        $this->assertEquals($expected, ExceptionLoggerSubscriber::getSubscribedEvents());
    }

    public function testOnException()
    {
        $exception = new \Exception('test');

        $logger = $this->createLoggerMock();
        $logger->expects($this->once())
            ->method('error')
            ->with('test', ['exception' => $exception]);

        $subscriber = new ExceptionLoggerSubscriber($logger);

        $request = $this->createRequestMock();
        $subscriber->onException(new ExceptionEvent($request, $exception));
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    public function createRequestMock(array $methods = [])
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
