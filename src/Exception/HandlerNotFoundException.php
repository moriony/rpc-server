<?php

namespace Moriony\RpcServer\Exception;

class HandlerNotFoundException extends \InvalidArgumentException
{
    protected $message = 'Handler was not found.';
}