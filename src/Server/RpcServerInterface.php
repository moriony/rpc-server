<?php

namespace Moriony\RpcServer\Server;

use Symfony\Component\HttpFoundation\Request;

interface RpcServerInterface
{
    public function handleRequest(Request $httpRequest);
}