<?php

namespace Moriony\RpcServer\ResponseSerializer;

class NativeJsonSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementationOf()
    {
        $serializer = new NativeJsonSerializer();
        $this->assertInstanceOf('Moriony\RpcServer\ResponseSerializer\SerializerInterface', $serializer);
    }

    /**
     * @dataProvider provideSerializeData
     */
    public function testSerialize($data, $expected)
    {
        $serializer = new NativeJsonSerializer();
        $this->assertEquals($expected, $serializer->serialize($data));
    }

    public function provideSerializeData()
    {
        return array(
            array(['test' => 1], json_encode(['test' => 1])),
            array(['test' => "abc"], json_encode(['test' => "abc"])),
            array(['test' => [1,2,3]], json_encode(['test' => [1,2,3]])),
            array(['test' => ["test" => 1]], json_encode(['test' => ["test" => 1]])),
        );
    }
}
