<?php

namespace Moriony\RpcServer\ResponseSerializer;

class XmlRpcSerializer implements SerializerInterface
{
    public function serialize($data)
    {
        return xmlrpc_encode($data);
    }
}