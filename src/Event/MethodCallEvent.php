<?php

namespace Moriony\RpcServer\Event;

use RpcServer\Request\RpcRequestInterface;
use Symfony\Component\EventDispatcher\Event;

class MethodCallEvent extends Event
{
    protected $request;
    protected $result;

    public function __construct(RpcRequestInterface $request, $result)
    {
        $this->request = $request;
        $this->result = $result;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResult()
    {
        return $this->result;
    }
}