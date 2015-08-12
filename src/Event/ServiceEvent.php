<?php

namespace Moriony\RpcServer\Event;

use RpcServer\Request\RpcRequestInterface;
use Symfony\Component\EventDispatcher\Event;

class ServiceEvent extends Event
{
    protected $request;
    protected $serviceMethod;

    public function __construct(RpcRequestInterface $request, $serviceMethod)
    {
        $this->request = $request;
        $this->serviceMethod = $serviceMethod;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getServiceMethod()
    {
        return $this->serviceMethod;
    }
}