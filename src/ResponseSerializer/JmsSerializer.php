<?php

namespace Moriony\RpcServer\ResponseSerializer;

use JMS\Serializer\SerializerInterface as JmsSerializerInterface;

class JmsSerializer implements SerializerInterface
{
    protected $serializer;
    protected $format;

    public function __construct(JmsSerializerInterface $serializer, $format)
    {
        $this->serializer = $serializer;
        $this->format = $format;
    }

    public function serialize($data)
    {
        return $this->serializer->serialize($data, $this->format);
    }
}