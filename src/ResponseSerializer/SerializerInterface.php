<?php

namespace Moriony\RpcServer\ResponseSerializer;

interface SerializerInterface
{
    public function serialize($data);
}