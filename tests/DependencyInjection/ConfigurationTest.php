<?php
/**
 * Date: 2018-12-20
 * Time: 01:44
 */

namespace DeliverymanBundleTest\DependencyInjection;


use DeliverymanBundle\DependencyInjection\Configuration;
use DeliverymanBundleTest\Service\CustomBatchService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaults()
    {
        $expected = [
            'instances' => [],
        ];

        $processor = new Processor();

        $actual = $processor->processConfiguration(new Configuration(), []);

        $this->assertEquals($expected, $actual);
    }

    public function configProvider()
    {
        return [
            [ // minimal setup
                'input' => [
                    'instances' => [
                        'custom' => [
                            'type' => 'default',
                        ],
                    ],
                ],
                'expected' => [
                    'instances' => [
                        'custom' => [
                            'type' => 'default',
                            'config' => [],
                            'service' => null,
                        ],
                    ],
                ],
            ],
            [ // setup with custom config
                'input' => [
                    'instances' => [
                        'default' => [
                            'type' => 'default',
                            'config' => [
                                'channels' => [
                                    'http_graph' => [
                                        'domains' => [
                                            'localhost',
                                            '127.0.0.1',
                                        ],
                                        'request_options' => [
                                            'allow_redirects' => false,
                                            'connect_timeout' => 10,
                                            'timeout' => 30,
                                            'debug' => false,
                                        ],
                                        'sender_headers' => [],
                                        'receiver_headers' => [],
                                        'expected_status_codes' => [
                                            200,
                                            204,
                                        ],
                                    ],
                                ],
                                'batch_format' => 'json',
                                'resource_format' => 'json',
                                'on_fail' => 'abort',
                                'config_merge' => 'first',
                                'silent' => false,
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    'instances' => [
                        'default' => [
                            'type' => 'default',
                            'config' => [
                                    'channels' => [
                                        'http_graph' => [
                                            'domains' => [
                                                'localhost',
                                                '127.0.0.1',
                                            ],
                                            'request_options' => [
                                                'allow_redirects' => false,
                                                'connect_timeout' => 10,
                                                'timeout' => 30,
                                                'debug' => false,
                                            ],
                                            'sender_headers' => [],
                                            'receiver_headers' => [],
                                            'expected_status_codes' => [200, 204,],
                                        ],
                                    ],
                                    'batch_format' => 'json',
                                    'resource_format' => 'json',
                                    'on_fail' => 'abort',
                                    'config_merge' => 'first',
                                    'silent' => false,
                                ],
                            'service' => null,
                        ],
                    ],
                ],
            ],
            [ // service setup
                'input' => [
                    'instances' => [
                        'default' => [
                            'type' => 'service',
                            'service' => CustomBatchService::class,
                        ],
                    ],
                ],
                'expected' => [
                    'instances' => [
                        'default' => [
                            'type' => 'service',
                            'config' => [],
                            'service' => 'DeliverymanBundleTest\\Service\\CustomBatchService',
                        ],
                    ],
                ],
            ],
            [ // service setup with custom config
                'input' => [
                    'instances' => [
                        'default' => [
                            'type' => 'service',
                            'service' => CustomBatchService::class,
                            'config' => ['test' => 'me']
                        ],
                    ],
                ],
                'expected' => [
                    'instances' => [
                        'default' => [
                            'type' => 'service',
                            'config' => ['test' => 'me'],
                            'service' => 'DeliverymanBundleTest\\Service\\CustomBatchService',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider configProvider
     * @param array $input
     * @param array $expected
     */
    public function testConfig(array $input, array $expected)
    {
        $processor = new Processor();

        $actual = $processor->processConfiguration(new Configuration(), [
            $input,
        ]);

//        echo '<pre>';
//        var_export($actual);
//        echo "\n";
//        print_r($actual);
////        print_r($input);
//        print_r($expected);
////        echo '</pre>';
//        die("\n" . __METHOD__ . ':' . __FILE__ . ':' . __LINE__ . "\n");

        $this->assertEquals($expected, $actual);
    }
}