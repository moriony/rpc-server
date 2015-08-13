<?php

namespace Moriony\RpcServer\ResponseSerializer;

use JMS\Serializer\SerializerInterface;

class JmsSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SerializerInterface
     */
    protected function createJmsSerializerMock()
    {
        return $this->getMockBuilder('JMS\Serializer\SerializerInterface')
            ->setMethods(array('serialize', 'deserialize'))
            ->getMock();
    }

    public function testImplementationOf()
    {
        $serializer = new JmsSerializer($this->createJmsSerializerMock(), 'json');
        $this->assertInstanceOf('Moriony\RpcServer\ResponseSerializer\SerializerInterface', $serializer);
    }

    /**
     * @dataProvider provideSerializeData
     */
    public function testSerialize($data, $format)
    {
        $jmsSerializerMock = $this->createJmsSerializerMock();
        $jmsSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($data), $this->equalTo($format))
            ->willReturn('test_result');

        $serializer = new JmsSerializer($jmsSerializerMock, $format);;
        $this->assertEquals('test_result', $serializer->serialize($data));
    }

    public function provideSerializeData()
    {
        return array(
            array(['test' => 1], 'json')
        );
    }
}
