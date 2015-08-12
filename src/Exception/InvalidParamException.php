<?php

namespace Moriony\RpcServer\Exception;

class InvalidParamException extends RpcException
{
    protected $message = 'Invalid method parameters.';
    protected $code = self::ERROR_CODE_INVALID_PARAM;
}