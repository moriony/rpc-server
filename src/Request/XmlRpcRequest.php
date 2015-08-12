<?php

namespace Moriony\RpcServer\Request;

use Moriony\RpcServer\Exception\InvalidRequestException;
use Moriony\RpcServer\Exception\RequestParseException;
use Symfony\Component\HttpFoundation\Request;

class XmlRpcRequest implements RpcRequestInterface
{
    const MESSAGE_INVALID_HTTP_METHOD = 'Invalid HTTP method, method should be POST.';
    const MESSAGE_INVALID_CONTENT_TYPE = 'Content-Type should be application/xml.';
    const MESSAGE_INVALID_BODY = 'Invalid request body, should be valid xml.';
    const MESSAGE_METHOD_REQUIRED = 'Invalid request body, should include method.';
    const MESSAGE_METHOD_PARAMS_REQUIRED = 'Invalid request body, should include params.';
    const MESSAGE_METHOD_PARAMS_TYPE = 'Invalid request body, params should be an array or object.';

    protected $httpRequest;
    protected $method;
    protected $params;
    protected $extraData;

    public function __construct(Request $request, array $extraData = [])
    {
        $this->httpRequest = $request;

        if (!$request->isMethod('POST')) {
            throw new InvalidRequestException(self::MESSAGE_INVALID_HTTP_METHOD);
        }
        if ($request->getContentType() != 'xml') {
            throw new InvalidRequestException(self::MESSAGE_INVALID_CONTENT_TYPE);
        }
        $this->params = xmlrpc_decode_request($request->getContent(), $this->method, 'UTF-8');

        if (is_null($this->method)) {
            throw new RequestParseException(self::MESSAGE_INVALID_BODY);
        }
        if (empty($this->method)) {
            throw new InvalidRequestException(self::MESSAGE_METHOD_REQUIRED);
        }
        if (!is_array($this->params)) {
            throw new InvalidRequestException(self::MESSAGE_METHOD_PARAMS_TYPE);
        }
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

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
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