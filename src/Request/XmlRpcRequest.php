<?php

namespace Moriony\RpcServer\Request;

use Symfony\Component\HttpFoundation\Request;

class XmlRpcRequest implements RpcRequestInterface
{
    protected $httpRequest;
    protected $method;
    protected $params;
    protected $extraData;

    public function __construct(Request $request, $method, array $params, array $extraData = [])
    {
        $this->httpRequest = $request;
        $this->method = $method;
        $this->params = $params;
        $this->extraData = $extraData;
    }

    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    public function get($name, $default = null)
    {
        return array_key_exists($name, $this->params) ? $this->params[$name] : $default;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getExtraData($name, $default = null)
    {
        return $this->hasExtraData($name) ? $this->extraData[$name] : $default;
    }

    public function setExtraData($name, $value)
    {
        $this->extraData[$name] = $value;
        return $this;
    }

    public function hasExtraData($name)
    {
        return array_key_exists($name, $this->extraData);
    }
}