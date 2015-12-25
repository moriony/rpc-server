<?php

namespace Moriony\RpcServer\Protocol;

use Exception;
use Moriony\RpcServer\Exception\RpcExceptionInterface;
use Moriony\RpcServer\Request\JsonRpcRequest;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\Response\JsonRpcResponse;
use Moriony\RpcServer\ResponseSerializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcProtocol implements ProtocolInterface
{
    const MESSAGE_UNEXPECTED_ERROR = 'Unexpected error occurred.';

    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @return JsonRpcRequest
     */
    public function createRequest(Request $request)
    {
        return new JsonRpcRequest($request);
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
        if ($exception instanceof RpcExceptionInterface) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $data = $exception->getData();
        } else {
            $code = RpcExceptionInterface::ERROR_CODE_INTERNAL_ERROR;
            $message = self::MESSAGE_UNEXPECTED_ERROR;
            $data = null;
        }

        $body = $this->serializer->serialize([
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data
            ],
            'id' => null
        ]);

        return new JsonRpcResponse($body, 200, []);
    }
}