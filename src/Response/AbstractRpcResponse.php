<?php

namespace Moriony\RpcServer\Response;

use Symfony\Component\HttpFoundation\Response;

abstract class AbstractRpcResponse extends Response
{
    abstract public function getData();
}