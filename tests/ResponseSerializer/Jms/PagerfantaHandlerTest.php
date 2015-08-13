<?php

namespace Moriony\RpcServer\ResponseSerializer;

use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;
use Moriony\RpcServer\ResponseSerializer\Jms\PagerfantaHandler;
use Pagerfanta\Pagerfanta;

class PagerfantaHandlerTest extends \PHPUnit_Framework_TestCase
{

    public function testImplementationOf()
    {
        $this->assertInstanceOf('JMS\Serializer\Handler\SubscribingHandlerInterface', new PagerfantaHandler());
    }

    public function testGetSubscribingMethods()
    {
        $expected = array(
            array(
                'direction' => 1,
                'format' => 'json',
                'type' => 'Pagerfanta\Pagerfanta',
                'method' => 'serializePagerfantaToJson',
            ),
        );
        $this->assertEquals($expected, PagerfantaHandler::getSubscribingMethods());
    }

    public function testPagerfantaCalls()
    {
        $visitor = $this->createVisitorMock();
        $visitor->method('visitArray')
            ->willReturn('test_items');

        $pagerfanta = $this->createPagerfantaMock();
        $pagerfanta->method('getNbPages')->willReturn('test_pages_count');
        $pagerfanta->method('getCurrentPage')->willReturn('test_current_page');
        $pagerfanta->method('getMaxPerPage')->willReturn('test_max_per_page');
        $pagerfanta->method('count')->willReturn('test_items_count');

        $context = $this->createContextMock();

        $handler = new PagerfantaHandler();
        $result = $handler->serializePagerfantaToJson($visitor, $pagerfanta, [], $context);
        $expected = array(
            'items' => 'test_items',
            'pages_count' => 'test_pages_count',
            'current_page' => 'test_current_page',
            'max_per_page' => 'test_max_per_page',
            'items_count' => 'test_items_count',
        );
        $this->assertEquals($expected, $result);
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Pagerfanta
     */
    public function createPagerfantaMock()
    {
        return $this->getMockBuilder('Pagerfanta\Pagerfanta')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getCurrentPageResults',
                'getNbPages',
                'getCurrentPage',
                'getMaxPerPage',
                'count'
            ))
            ->getMock();
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JsonSerializationVisitor
     */
    public function createVisitorMock()
    {
        return $this->getMockBuilder('JMS\Serializer\JsonSerializationVisitor')
            ->disableOriginalConstructor()
            ->setMethods(array('visitArray'))
            ->getMock();
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Context
     */
    public function createContextMock()
    {
        return $this->getMockBuilder('JMS\Serializer\Context')
            ->getMock();
    }
}
