<?php

namespace Moriony\RpcServer\Server;

use Moriony\RpcServer\Exception\RpcExceptionInterface;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\Request\XmlRpcRequest;
use Moriony\RpcServer\Response\XmlRpcResponse;
use Symfony\Component\HttpFoundation\Request;

class XmlRpcServer extends AbstractRpcServer implements RpcServerInterface
{
    const MESSAGE_UNEXPECTED_ERROR = 'Unexpected error occurred.';

    protected function createRequest(Request $request)
    {
        return new XmlRpcRequest($request);
    }

    protected function createResponse($data)
    {
        return new XmlRpcResponse($data, 200, []);
    }

    protected function prepareResponseData(RpcRequestInterface $request, $data)
    {
        return $data;
    }

    protected function prepareErrorResponseData(\Exception $exception)
    {
        if ($exception instanceof RpcExceptionInterface) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
        } else {
            $code = RpcExceptionInterface::ERROR_CODE_INTERNAL_ERROR;
            $message = self::MESSAGE_UNEXPECTED_ERROR;
        }
        return [
            'faultCode' => $code,
            'faultString' => $message,
        ];
    }
}