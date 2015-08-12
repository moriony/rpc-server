<?php

namespace Moriony\RpcServer\Request;

use Moriony\RpcServer\Exception\InvalidRequestException;
use Moriony\RpcServer\Exception\RequestParseException;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcRequest implements RpcRequestInterface
{
    const MESSAGE_INVALID_HTTP_METHOD = 'Invalid HTTP method, method should be POST.';
    const MESSAGE_INVALID_CONTENT_TYPE = 'Content-Type should be application/json.';
    const MESSAGE_INVALID_BODY = 'Invalid request body, should be valid json.';
    const MESSAGE_ID_REQUIRED = 'Invalid request body, should include id.';
    const MESSAGE_METHOD_REQUIRED = 'Invalid request body, should include method.';
    const MESSAGE_METHOD_PARAMS_REQUIRED = 'Invalid request body, should include params.';
    const MESSAGE_METHOD_PARAMS_TYPE = 'Invalid request body, params should be an array or object.';
    const MESSAGE_JSON_RPC_REQUIRED = 'Invalid request body, should include jsonrpc.';

    protected $httpRequest;
    protected $data;
    protected $extraData;

    public function __construct(Request $request, array $extraData = [])
    {
        $this->httpRequest = $request;

        if (!$request->isMethod('POST')) {
            throw new InvalidRequestException(self::MESSAGE_INVALID_HTTP_METHOD);
        }
        if ($request->getContentType() != 'json') {
            throw new InvalidRequestException(self::MESSAGE_INVALID_CONTENT_TYPE);
        }
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            throw new RequestParseException(self::MESSAGE_INVALID_BODY);
        }
        if (empty($data['jsonrpc'])) {
            throw new InvalidRequestException(self::MESSAGE_JSON_RPC_REQUIRED);
        }
        if (empty($data['id'])) {
            throw new InvalidRequestException(self::MESSAGE_ID_REQUIRED);
        }
        if (empty($data['method'])) {
            throw new InvalidRequestException(self::MESSAGE_METHOD_REQUIRED);
        }
        if (!isset($data['params'])) {
            throw new InvalidRequestException(self::MESSAGE_METHOD_PARAMS_REQUIRED);
        }
        if (!is_array($data['params'])) {
            throw new InvalidRequestException(self::MESSAGE_METHOD_PARAMS_TYPE);
        }
        $this->data = $data;
        $this->extraData = $extraData;
    }

    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    public function getData()
    {
        return $this->data;
    }

    public function get($name, $default = null)
    {
        return array_key_exists($name, $this->data['params']) ? $this->data['params'][$name] : $default;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function setId($id)
    {
        $this->data['id'] = $id;
        return $this;
    }

    public function getProtocolVersion()
    {
        return $this->data['jsonrpc'];
    }

    public function setProtocolVersion($protocolVersion)
    {
        $this->data['jsonrpc'] = $protocolVersion;
        return $this;
    }

    public function getMethod()
    {
        return $this->data['method'];
    }

    public function setMethod($method)
    {
        $this->data['method'] = $method;
        return $this;
    }

    public function getParams()
    {
        return $this->data['params'];
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