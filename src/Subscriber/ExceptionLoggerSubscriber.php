<?php

namespace Moriony\RpcServer\Subscriber;

use Psr\Log\LoggerInterface;
use Moriony\RpcServer\Event\ExceptionEvent;
use Moriony\RpcServer\Server\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceptionLoggerSubscriber implements EventSubscriberInterface
{
    protected $logger;
    protected $exclusion;

    /**
     * @param LoggerInterface $logger
     * @param mixed $exclusion
     */
    public function __construct(LoggerInterface $logger, $exclusion = null)
    {
        $this->logger = $logger;
        $this->exclusion = $exclusion;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_RPC_EXCEPTION => [['onException']],
        ];
    }

    public function onException(ExceptionEvent $event)
    {
        $exclusion = $this->exclusion;
        if (!is_null($exclusion)) {
            if (!is_callable($exclusion)) {
                throw new \RuntimeException('Invalid exclusion.');
            }
            if (call_user_func($exclusion, $event)) {
                return;
            }
        }

        $this->logger->error($event->getException()->getMessage(), [
            'exception' => $event->getException()
        ]);
    }
}