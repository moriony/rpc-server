<?php

namespace Moriony\RpcServer\Protocol;

use Exception;
use Moriony\RpcServer\Exception\RequestParseException;
use Moriony\RpcServer\Exception\RpcException;
use Moriony\RpcServer\Request\RpcRequestInterface;
use Moriony\RpcServer\Response\JsonRpcResponse;
use Symfony\Component\HttpFoundation\Request;

class MultiProtocol implements ProtocolInterface
{
    /**
     * @var ProtocolInterface[]
     */
    protected $protocols;

    /**
     * @var ProtocolInterface
     */
    protected $protocol;

    /**
     * @param ProtocolInterface[] $protocols
     */
    public function __construct(array $protocols)
    {
        $this->setProtocols($protocols);
    }

    /**
     * @return string
     */
    public function getName()
    {
        $protocolNames = [];
        foreach ($this->protocols as $protocol) {
            $protocolNames[] = $protocol->getName();
        }

        return implode(', ', $protocolNames);
    }

    /**
     * @param ProtocolInterface[] $protocols
     * @return $this
     */
    public function setProtocols(array $protocols)
    {
        $this->protocol = null;
        $this->protocols = [];
        foreach ($protocols as $protocol) {
            $this->addProtocol($protocol);
        }

        return $this;
    }

    /**
     * @return ProtocolInterface[]
     */
    public function getProtocols()
    {
        return $this->protocols;
    }

    /**
     * @param ProtocolInterface $protocol
     * @return $this
     */
    public function addProtocol(ProtocolInterface $protocol)
    {
        $this->protocols[] = $protocol;

        return $this;
    }

    /**
     * @return ProtocolInterface
     * @throws RequestParseException
     */
    public function getDetectedProtocol()
    {
        if (!$this->protocol) {
            throw new RequestParseException('Protocol not detected.');
        }

        return $this->protocol;
    }

    /**
     * @param Request $request
     * @return RpcRequestInterface
     * @throws RequestParseException
     */
    public function createRequest(Request $request)
    {
        $rpcRequest = null;
        $this->protocol = null;

        foreach ($this->protocols as $protocol) {
            try {
                $rpcRequest = $protocol->createRequest($request);
                $this->protocol = $protocol;
            } catch (RpcException $e) {
                $rpcRequest = null;
                $this->protocol = $protocol;
            }
        }

        if (!$rpcRequest) {
            $message = sprintf('Protocol not supported. Available protocols: %s', $this->getName());
            throw new RequestParseException($message);
        }

        return $rpcRequest;
    }

    /**
     * @param RpcRequestInterface $request
     * @param mixed $data
     * @return JsonRpcResponse
     */
    public function createResponse(RpcRequestInterface $request, $data)
    {
        return $this->getDetectedProtocol()->createResponse($request, $data);
    }

    /**
     * @param Exception $exception
     * @return JsonRpcResponse
     */
    public function createErrorResponse(\Exception $exception)
    {
        return $this->getDetectedProtocol()->createErrorResponse($exception);
    }
}