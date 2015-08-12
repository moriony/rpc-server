<?php

namespace Moriony\RpcServer\Exception;

class RpcException extends \Exception implements RpcExceptionInterface
{
    protected $message = 'Unexpected error occurred.';
    protected $code = self::ERROR_CODE_INTERNAL_ERROR;
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
}