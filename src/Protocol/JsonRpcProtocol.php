<?php

namespace Moriony\RpcServer\Protocol;

use Exception;
use Moriony\RpcServer\Exception\InvalidRequestException;
use Moriony\RpcServer\Exception\RequestParseException;
use Moriony\RpcServer\Exception\RpcException;
use Moriony\RpcServer\Exception\RpcExceptionInterface;
use Moriony\RpcServer\Request\JsonRpcRequest;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\Response\JsonRpcResponse;
use Moriony\RpcServer\ResponseSerializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcProtocol implements ProtocolInterface
{
    const MESSAGE_UNEXPECTED_ERROR = 'Unexpected error occurred.';
    const MESSAGE_INVALID_HTTP_METHOD = 'Invalid HTTP method, method should be POST.';
    const MESSAGE_INVALID_CONTENT_TYPE = 'Content-Type should be application/json.';
    const MESSAGE_INVALID_BODY = 'Invalid request body, should be valid json.';
    const MESSAGE_ID_REQUIRED = 'Invalid request body, should include id.';
    const MESSAGE_METHOD_REQUIRED = 'Invalid request body, should include method.';
    const MESSAGE_METHOD_PARAMS_REQUIRED = 'Invalid request body, should include params.';
    const MESSAGE_METHOD_PARAMS_TYPE = 'Invalid request body, params should be an array or object.';
    const MESSAGE_JSON_RPC_REQUIRED = 'Invalid request body, should include jsonrpc.';

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
        return 'JSON-RPC 2.0';
    }

    /**
     * @param Request $request
     * @return JsonRpcRequest
     * @throws RpcException
     */
    public function createRequest(Request $request)
    {
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

        return new JsonRpcRequest($request, $data['jsonrpc'], $data['id'], $data['method'], $data['params']);
    }

    /**
     * @param RpcRequestInterface $request
     * @param mixed $data
     * @return JsonRpcResponse
     */
    public function createResponse(RpcRequestInterface $request, $data)
    {
        /** @var JsonRpcRequest $request */
        $body = $this->serializer->serialize([
            'jsonrpc' => '2.0',
            'result' => $data,
            'id' => $request->getId()
        ]);

        return new JsonRpcResponse($body, 200, []);
    }

    /**
     * @param Exception $exception
     * @return JsonRpcResponse
     */
    public function createErrorResponse(\Exception $exception)
    {
        if (!$exception instanceof RpcExceptionInterface) {
            $exception = new RpcException();
        }

        $body = $this->serializer->serialize([
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'data' => $exception->getData()
            ],
            'id' => null
        ]);

        return new JsonRpcResponse($body, 200, []);
    }
}