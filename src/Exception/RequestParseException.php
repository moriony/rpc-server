<?php

namespace Moriony\RpcServer\Exception;

class RequestParseException extends RpcException
{
    protected $message = 'Request parsing error occurred.';
    protected $code = self::ERROR_CODE_PARSE;
}