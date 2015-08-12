<?php

namespace Moriony\RpcServer\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class ExceptionEvent extends Event
{
    protected $request;
    protected $exception;

    public function __construct(Request $request, \Exception $e)
    {
        $this->request = $request;
        $this->exception = $e;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getException()
    {
        return $this->exception;
    }
}