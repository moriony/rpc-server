<?php

namespace Moriony\RpcServer\Exception;

class InvalidMethodException extends RpcException
{
    protected $message = 'Invalid method.';
    protected $code = self::ERROR_CODE_INVALID_METHOD;
}