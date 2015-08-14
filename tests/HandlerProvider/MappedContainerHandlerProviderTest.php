<?php

namespace Moriony\RpcServer\Handler;

use Moriony\RpcServer\HandlerProvider\MappedContainerHandlerProvider;
use Symfony\Component\DependencyInjection\Container;

class MappedContainerHandlerProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvideHandlersClass()
    {
        $container = new Container();
        $provider = new MappedContainerHandlerProvider($container, [
            'map' => [
                'handlerName' => [
                    'service' => 'serviceName',
                    'method' => 'methodName'
                ]
            ]
        ]);

        $handlers = $provider->provide();
        $this->assertInstanceOf('Moriony\RpcServer\Handler\ServiceContainerHandler', $handlers['handlerName']);
    }

    /**
     * @dataProvider provideHandlersCountTestData
     */
    public function testProvideHandlersCounts($config, $expectedCount)
    {
        $container = new Container();
        $provider = new MappedContainerHandlerProvider($container, $config);
        $this->assertCount($expectedCount, $provider->provide());
    }

    public function provideHandlersCountTestData()
    {
        return [
            // Case 1
            [
                [
                    'map' => []
                ],
                0
            ],
            // Case 2
            [
                [
                    'map' => [
                        'handlerName' => [
                            'service' => 'serviceName',
                            'method' => 'methodName'
                        ]
                    ]
                ],
                1
            ],
            // Case 3
            [
                [
                    'map' => [
                        'handlerName1' => [
                            'service' => 'serviceName',
                            'method' => 'methodName'
                        ],
                        'handlerName2' => [
                            'service' => 'serviceName',
                            'method' => 'methodName'
                        ]
                    ]
                ],
                2
            ],
        ];
    }
}
