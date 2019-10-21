<?php
/**
 * Date: 2018-12-27
 * Time: 16:00
 */

namespace DeliverymanBundleTest\DependencyInjection;

use Deliveryman\Channel\ChannelInterface;
use Deliveryman\Channel\HttpGraphChannel;
use Deliveryman\Normalizer\BatchRequestNormalizer;
use Deliveryman\Service\BatchRequestHandler;
use Deliveryman\Service\BatchRequestHandlerInterface;
use DeliverymanBundle\DependencyInjection\DeliverymanExtension;
use DeliverymanBundleTest\Service\CustomInstanceService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class DeliverymanExtensionTest extends TestCase
{
    /**
     * XSD check
     */
    public function testGetXsdValidationBasePath()
    {
        $extension = new DeliverymanExtension();
        $this->assertFalse($extension->getXsdValidationBasePath());
    }

    /**
     * Alias check
     */
    public function testGetAlias()
    {
        $extension = new DeliverymanExtension();
        $this->assertEquals('deliveryman', $extension->getAlias());
    }

    /**
     * @throws \Exception
     */
    public function testDefaultLoad()
    {
        $container = $this->getContainer([
            [
                'instances' => [
                    'custom' => [
                        'type' => 'default',
                    ],
                ],
            ]
        ]);

        $this->assertTrue($container->has('deliveryman.handler.http_graph.custom'));
        $this->assertInstanceOf(BatchRequestHandler::class, $container->get('deliveryman.handler.http_graph.custom'));
        $this->assertInstanceOf(BatchRequestHandlerInterface::class, $container->get('deliveryman.handler.http_graph.custom'));

        $this->assertTrue($container->has('deliveryman.channel.http_graph.custom'));
        $this->assertInstanceOf(HttpGraphChannel::class, $container->get('deliveryman.channel.http_graph.custom'));
        $this->assertInstanceOf(ChannelInterface::class, $container->get('deliveryman.channel.http_graph.custom'));
        $this->assertTrue($container->hasDefinition('deliveryman.channel.http_graph.custom'));

        $this->assertTrue($container->has('deliveryman.normalizer'));

        /** @var BatchRequestNormalizer $normalizer */
        $normalizer = $container->get('deliveryman.normalizer');
        $this->assertInstanceOf(BatchRequestNormalizer::class, $normalizer);
    }

    public function loadFails()
    {
        return [
            [
                [ // instances
                    'svc' => [
                        'type' => 'service',
                    ],
                ],
                [],
                InvalidArgumentException::class,
                'Service ID is not defined for instance: svc'
            ],
            [
                [ // instances
                    'svc' => [
                        'type' => 'service',
                        'service' => null,
                    ],
                ],
                [],
                InvalidArgumentException::class,
                'Service ID is not defined for instance: svc'
            ],
            [
                [ // instances
                    'svc' => [
                        'type' => 'service',
                        'service' => 'UnknownClassName',
                    ],
                ],
                [],
                InvalidArgumentException::class,
                'Cannot bootstrap unknown service: UnknownClassName'
            ],
            [
                [ // instances
                    'svc' => [
                        'type' => 'service',
                        'service' => 'random_svc',
                    ],
                ],
                ['random_svc' => new Definition(\Exception::class)],
                InvalidArgumentException::class,
                'Class "Exception" must implement interface of "DeliverymanBundle\DependencyInjection\InstanceBootstrap\ServiceBootstrapperInterface" to instantiate batch: svc'
            ],
        ];
    }

    /**
     * @param array $instances
     * @param Definition[] $definitions
     * @param $exceptionClass
     * @param $exceptionMessage
     * @throws \Exception
     * @dataProvider loadFails
     */
    public function testLoadFail(array $instances, array $definitions, $exceptionClass, $exceptionMessage)
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);
        $this->getContainer([['instances' => $instances]], $definitions);
    }

    /**
     * @throws \Exception
     */
    public function testServiceLoad()
    {
        $container = $this->getContainer([
            [
                'instances' => [
                    'custom_svc' => [
                        'type' => 'service',
                        'service' => CustomInstanceService::class,
                    ],
                ],
            ]
        ], [CustomInstanceService::class => (new Definition(CustomInstanceService::class))->setPublic(true)]);

        $this->assertTrue($container->has(CustomInstanceService::class));
        $this->assertTrue($container->has('deliveryman.service.custom_svc'));
        $this->assertNotEmpty($container->getAlias('deliveryman.service.custom_svc'));
    }

    /**
     * Check init extension namespace
     */
    public function testGetNamespace()
    {
        $extension = new DeliverymanExtension();
        $this->assertEquals('http://www.example.com/schema/deliveryman', $extension->getNamespace());
    }

    /**
     * @throws \Exception
     */
    public function testInvalidChannelTag()
    {
        $this->expectExceptionMessage('Alias for channel tags must be set');

        $definition = new Definition(HttpGraphChannel::class);
        $definition->addTag('deliveryman.channel');

        $this->getContainer([
            [
                'instances' => [
                    'custom' => [
                        'type' => 'default',
                    ],
                ],
            ]
        ], [$definition]);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidChannelClass()
    {
        $this->expectExceptionMessage('Service "badChannel" must implement interface "Deliveryman\Channel\ChannelInterface".');

        $definition = new Definition(\stdClass::class);
        $definition->addTag('deliveryman.channel', ['channel' => 'http_graph']);

        $this->getContainer([
            [
                'instances' => [
                    'custom' => [
                        'type' => 'default',
                    ],
                ],
            ]
        ], ['badChannel' => $definition]);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidChannelNormalizerClass()
    {
        $this->expectExceptionMessage('Service "badChannelNormalizer" must implement interface "Deliveryman\Normalizer\ChannelNormalizerInterface".');

        $definition = new Definition(\stdClass::class);
        $definition->addTag('deliveryman.channel_normalizer');

        $this->getContainer([
            [
                'instances' => [
                    'custom' => [
                        'type' => 'default',
                    ],
                ],
            ]
        ], ['badChannelNormalizer' => $definition]);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidHandlerTag()
    {
        $this->expectExceptionMessage('Sender tags for channel must be set');

        $definition = new Definition(BatchRequestHandler::class);
        $definition->addTag('deliveryman.handler');

        $this->getContainer([
            [
                'instances' => [
                    'custom' => [
                        'type' => 'default',
                    ],
                ],
            ]
        ], ['badInstance' => $definition]);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidHandlerClass()
    {
        $this->expectExceptionMessage('Service "badSender" must implement interface "Deliveryman\Service\BatchRequestHandlerInterface".');

        $definition = new Definition(\stdClass::class);
        $definition->addTag('deliveryman.handler', ['channel' => 'http_graph']);

        $this->getContainer([
            [
                'instances' => [
                    'custom' => [
                        'type' => 'default',
                    ],
                ],
            ]
        ], ['badSender' => $definition]);
    }

    /**
     * @param array $configs
     * @param array $thirdPartyDefinitions
     * @return ContainerBuilder
     * @throws \Exception
     */
    protected function getContainer(array $configs = [], array $thirdPartyDefinitions = [])
    {
        $container = new ContainerBuilder();
        foreach ($thirdPartyDefinitions as $id => $definition) {
            $container->setDefinition($id, $definition);
        }

        $loader = new DeliverymanExtension();
        $loader->load($configs, $container);

        $this->addSerializer($container);

        $container->compile();

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addSerializer(ContainerBuilder $container): void
    {
        $encoderDef = new Definition(JsonEncoder::class);
        $encoderDef->addTag('serializer.encoder');
        $container->setDefinition('serializer.encoder.json', $encoderDef);

        $definition = new Definition(Serializer::class);
        $definition->setArgument(0, []);
        $definition->setArgument(1, []);
        $container->setDefinition('serializer', $definition);
        $container->addCompilerPass(new SerializerPass());
    }
}
