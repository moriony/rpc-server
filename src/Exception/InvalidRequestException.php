<?php

namespace Moriony\RpcServer\Exception;

class InvalidRequestException extends RpcException
{
    protected $message = 'Invalid request.';
    protected $code = self::ERROR_CODE_INVALID_REQUEST;
}