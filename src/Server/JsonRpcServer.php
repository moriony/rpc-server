<?php

namespace Moriony\RpcServer\Server;

use Moriony\RpcServer\Exception\RpcExceptionInterface;
use Moriony\RpcServer\Request\JsonRpcRequest;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\Response\JsonRpcResponse;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcServer extends AbstractRpcServer implements RpcServerInterface
{
    const MESSAGE_UNEXPECTED_ERROR = 'Unexpected error occurred.';

    protected function createRequest(Request $request)
    {
        return new JsonRpcRequest($request, $this->config['extra_data']);
    }

    protected function createResponse($data)
    {
        return new JsonRpcResponse($data, 200, []);
    }

    protected function prepareResponseData(RpcRequestInterface $request, $data)
    {
        /** @var JsonRpcRequest $request */
        return [
            'jsonrpc' => '2.0',
            'result' => $data,
            'id' => $request->getId()
        ];
    }

    protected function prepareErrorResponseData(\Exception $exception)
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
        return [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data
            ],
            'id' => null
        ];
    }
}