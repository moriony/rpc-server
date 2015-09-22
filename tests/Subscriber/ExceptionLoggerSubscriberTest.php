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

    public function testExclusionException()
    {
        $this->setExpectedException('RuntimeException', 'Invalid exclusion.');

        $exception = new \Exception('test');

        $logger = $this->createLoggerMock();
        $subscriber = new ExceptionLoggerSubscriber($logger, 'invalid_function');

        $request = $this->createRequestMock();
        $subscriber->onException(new ExceptionEvent($request, $exception));
    }

    public function testExclusionCall()
    {
        $phpunit = $this;
        $exception = new \Exception('test');
        $request = $this->createRequestMock();
        $event = new ExceptionEvent($request, $exception);
        $subscriber = new ExceptionLoggerSubscriber($this->createLoggerMock(), function ($e) use($phpunit, $event) {
            $phpunit->assertSame($e, $event);
        });
        $subscriber->onException($event);
    }

    public function testExclusionFail()
    {
        $exception = new \Exception('test');

        $logger = $this->createLoggerMock();
        $logger->expects($this->once())
            ->method('error');

        $subscriber = new ExceptionLoggerSubscriber($logger, function () {
            return false;
        });
        $request = $this->createRequestMock();
        $subscriber->onException(new ExceptionEvent($request, $exception));
    }

    public function testExclusionSuccess()
    {
        $exception = new \Exception('test');

        $logger = $this->createLoggerMock();
        $logger->expects($this->exactly(0))
            ->method('error');

        $subscriber = new ExceptionLoggerSubscriber($logger, function () {
            return true;
        });

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
