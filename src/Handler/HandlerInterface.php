<?php

namespace Moriony\RpcServer\Handler;

use Moriony\RpcServer\Request\RpcRequestInterface;

interface HandlerInterface
{
    public function handle(RpcRequestInterface $requestInterface);
}