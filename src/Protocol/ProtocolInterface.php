<?php

namespace Moriony\RpcServer\Protocol;

use Moriony\RpcServer\Request\RpcRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use \Exception;
use Symfony\Component\HttpFoundation\Response;

interface ProtocolInterface
{
    /**
     * @param Request $request
     * @return RpcRequestInterface
     */
    public function createRequest(Request $request);

    /**
     * @param RpcRequestInterface $request
     * @param mixed $data
     * @return Response
     */
    public function createResponse(RpcRequestInterface $request, $data);

    /**
     * @param Exception $exception
     * @return Response
     */
    public function createErrorResponse(Exception $exception);
}
