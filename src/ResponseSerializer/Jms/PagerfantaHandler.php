<?php

namespace Moriony\RpcServer\ResponseSerializer\Jms;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;
use Pagerfanta\Pagerfanta;

class PagerfantaHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Pagerfanta\Pagerfanta',
                'method' => 'serializePagerfantaToJson',
            ],
        ];
    }

    public function serializePagerfantaToJson(JsonSerializationVisitor $visitor, Pagerfanta $pagerfanta, array $type, Context $context)
    {
        $type['name'] = 'array';
        return [
            'items' => $visitor->visitArray((array) $pagerfanta->getCurrentPageResults(), $type, $context),
            'pages_count' => $pagerfanta->getNbPages(),
            'current_page' => $pagerfanta->getCurrentPage(),
            'max_per_page' => $pagerfanta->getMaxPerPage(),
            'items_count' => $pagerfanta->count(),
        ];
    }
}