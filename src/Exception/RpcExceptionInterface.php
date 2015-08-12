<?php

namespace Moriony\RpcServer\Exception;

interface RpcExceptionInterface
{
    const ERROR_CODE_PARSE = -32700;
    const ERROR_CODE_INVALID_REQUEST = -32600;
    const ERROR_CODE_INVALID_METHOD = -32601;
    const ERROR_CODE_INVALID_PARAM = -32602;
    const ERROR_CODE_INTERNAL_ERROR = -32603;

    public function getMessage();
    public function getCode();
    public function getData();
}