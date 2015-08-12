<?php

namespace Moriony\RpcServer\Subscriber;

use Moriony\RpcServer\Event\HttpRequestEvent;
use Moriony\RpcServer\Exception\InvalidRequestException;
use Moriony\RpcServer\Server\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GetParamAuthenticationSubscriber implements EventSubscriberInterface
{
    const DEFAULT_PARAM_NAME = 'key';
    const MESSAGE_AUTHENTICATION_FAILED = "Authentication failed.";

    protected $paramName;
    protected $key;

    public function __construct($key, $paramName = self::DEFAULT_PARAM_NAME)
    {
        $this->key = $key;
        $this->paramName = $paramName;
    }

    public function authentication(HttpRequestEvent $event)
    {
        if ($event->getRequest()->get($this->paramName) !== $this->key) {
            throw new InvalidRequestException(self::MESSAGE_AUTHENTICATION_FAILED);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_HTTP_REQUEST => [['authentication']],
        ];
    }
}