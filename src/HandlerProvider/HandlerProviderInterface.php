<?php

namespace Moriony\RpcServer\HandlerProvider;

use Moriony\RpcServer\Handler\HandlerInterface;

interface HandlerProviderInterface
{
    /** @return HandlerInterface[] */
    public function provide();
}