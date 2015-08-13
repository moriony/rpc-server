<?php

namespace Moriony\RpcServer\ResponseSerializer;

class XmlRpcSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementationOf()
    {
        $serializer = new XmlRpcSerializer();
        $this->assertInstanceOf('Moriony\RpcServer\ResponseSerializer\SerializerInterface', $serializer);
    }

    /**
     * @dataProvider provideSerializeData
     */
    public function testSerialize($data, $expected)
    {
        $serializer = new XmlRpcSerializer();
        $this->assertEquals($expected, $serializer->serialize($data));
    }

    public function provideSerializeData()
    {
        return array(
            array(['test' => 1], xmlrpc_encode(['test' => 1])),
            array(['test' => "abc"], xmlrpc_encode(['test' => "abc"])),
            array(['test' => [1,2,3]], xmlrpc_encode(['test' => [1,2,3]])),
            array(['test' => ["test" => 1]], xmlrpc_encode(['test' => ["test" => 1]])),
        );
    }
}
