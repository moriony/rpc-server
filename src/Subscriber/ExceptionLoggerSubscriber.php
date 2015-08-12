<?php

namespace Moriony\RpcServer\Subscriber;

use Psr\Log\LoggerInterface;
use Moriony\RpcServer\Event\ExceptionEvent;
use Moriony\RpcServer\Server\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceptionLoggerSubscriber implements EventSubscriberInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_RPC_EXCEPTION => [['onException']],
        ];
    }

    public function onException(ExceptionEvent $event)
    {
        $this->logger->error($event->getException()->getMessage(), [
            'exception' => $event->getException()
        ]);
    }
}