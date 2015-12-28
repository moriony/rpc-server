<?php

namespace Moriony\RpcServer\Protocol;

use Exception;
use Moriony\RpcServer\Exception\InvalidRequestException;
use Moriony\RpcServer\Exception\RequestParseException;
use Moriony\RpcServer\Exception\RpcException;
use Moriony\RpcServer\Exception\RpcExceptionInterface;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\Request\XmlRpcRequest;
use Moriony\RpcServer\Response\XmlRpcResponse;
use Moriony\RpcServer\ResponseSerializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

class XmlRpcProtocol implements ProtocolInterface
{
    const MESSAGE_UNEXPECTED_ERROR = 'Unexpected error occurred.';
    const MESSAGE_INVALID_HTTP_METHOD = 'Invalid HTTP method, method should be POST.';
    const MESSAGE_INVALID_CONTENT_TYPE = 'Content-Type should be application/xml.';
    const MESSAGE_INVALID_BODY = 'Invalid request body, should be valid xml.';
    const MESSAGE_METHOD_REQUIRED = 'Invalid request body, should include method.';
    const MESSAGE_METHOD_PARAMS_TYPE = 'Invalid request body, params should be an array or object.';

    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'XML-RPC';
    }

    /**
     * @param Request $request
     * @return XmlRpcRequest
     * @throws RpcException
     */
    public function createRequest(Request $request)
    {
        if (!$request->isMethod('POST')) {
            throw new InvalidRequestException(self::MESSAGE_INVALID_HTTP_METHOD);
        }
        if ($request->getContentType() != 'xml') {
            throw new InvalidRequestException(self::MESSAGE_INVALID_CONTENT_TYPE);
        }
        $params = xmlrpc_decode_request($request->getContent(), $method, 'UTF-8');
        if (is_null($method)) {
            throw new RequestParseException(self::MESSAGE_INVALID_BODY);
        }
        if (empty($method)) {
            throw new InvalidRequestException(self::MESSAGE_METHOD_REQUIRED);
        }
        if (!is_array($params)) {
            throw new InvalidRequestException(self::MESSAGE_METHOD_PARAMS_TYPE);
        }

        return new XmlRpcRequest($request, $method, $params);
    }

    /**
     * @param RpcRequestInterface $request
     * @param mixed $data
     * @return XmlRpcResponse
     */
    public function createResponse(RpcRequestInterface $request, $data)
    {
        $body = $this->serializer->serialize($data);
        return new XmlRpcResponse($body, 200, []);
    }

    /**
     * @param Exception $exception
     * @return XmlRpcResponse
     */
    public function createErrorResponse(Exception $exception)
    {
        if (!$exception instanceof RpcExceptionInterface) {
            $exception = new RpcException();
        }
        $body = $this->serializer->serialize([
            'faultCode' => $exception->getCode(),
            'faultString' => $exception->getMessage(),
        ]);
        return new XmlRpcResponse($body, 200, []);
    }
}