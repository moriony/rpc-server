<?php

namespace Moriony\RpcServer\ResponseSerializer;

class NativeJsonSerializer implements SerializerInterface
{
    public function serialize($data)
    {
        return json_encode($data);
    }
}