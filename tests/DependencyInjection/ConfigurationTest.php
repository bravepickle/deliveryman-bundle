<?php
/**
 * Date: 2018-12-20
 * Time: 01:44
 */

namespace DeliverymanBundleTest\DependencyInjection;


use DeliverymanBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaults()
    {
        $expected = [
            'instances' => [
                'default' => [
                    'domains' => [],
                    'channels' => [
                        'http' => [
                            'request_options' => [
                                'allow_redirects' => false,
                                'connect_timeout' => 10,
                                'timeout' => 30,
                                'debug' => false,
                            ],
                            'sender_headers' => [],
                            'receiver_headers' => [],
                        ],
                    ],
                    'batch_format' => 'json',
                    'resource_format' => 'json',
                    'on_fail' => 'abort',
                    'config_merge' => 'first',
                    'expected_status_codes' => [
                        200,
                        201,
                        202,
                        204,
                    ],
                    'methods' => [
                        0 => 'GET',
                        1 => 'POST',
                    ],
                    'silent' => false,
                    'forward_master_headers' => true,
                ],
            ],
        ];

        $processor = new Processor();

        $actual = $processor->processConfiguration(new Configuration(), []);

        $this->assertEquals($expected, $actual);
    }
}