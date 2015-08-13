<?php

namespace Moriony\RpcServer\Handler;

use Moriony\RpcServer\Request\RpcRequestInterface;

class ClosureHandler implements HandlerInterface
{
    /** @var \Closure */
    protected $closure;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function handle(RpcRequestInterface $request)
    {
        $closure = $this->closure;
        return $closure($request);
    }
}