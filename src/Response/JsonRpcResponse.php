<?php

namespace Moriony\RpcServer\Response;

use Symfony\Component\HttpFoundation\Response;

class JsonRpcResponse extends Response
{
    public function __construct($content = '', $status = 200, $headers = array())
    {
        parent::__construct($content, $status, $headers);
        $this->headers->set('Content-Type', 'application/json');
    }
}