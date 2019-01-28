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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
    public function testLoad()
    {
        $container = $this->getContainer([
            ['instances' => ['default' => ['domains' => ['localhost']]]]
        ]);

        $this->assertTrue($container->has('deliveryman.handler.http_graph.default'));
        $this->assertInstanceOf(BatchRequestHandler::class, $container->get('deliveryman.handler.http_graph.default'));
        $this->assertInstanceOf(BatchRequestHandlerInterface::class, $container->get('deliveryman.handler.http_graph.default'));


        $this->assertTrue($container->has('deliveryman.channel.http_graph.default'));
        $this->assertInstanceOf(HttpGraphChannel::class, $container->get('deliveryman.channel.http_graph.default'));
        $this->assertInstanceOf(ChannelInterface::class, $container->get('deliveryman.channel.http_graph.default'));
        $this->assertTrue($container->hasDefinition('deliveryman.channel.http_graph.default'));

        $this->assertTrue($container->has('deliveryman.normalizer'));
        /** @var BatchRequestNormalizer $normalizer */
        $normalizer = $container->get('deliveryman.normalizer');
        $this->assertInstanceOf(BatchRequestNormalizer::class, $normalizer);
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
            ['instances' => ['default' => ['domains' => ['localhost']]]]
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
            ['instances' => ['default' => ['domains' => ['localhost']]]]
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
            ['instances' => ['default' => ['domains' => ['localhost']]]]
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
            ['instances' => ['default' => ['domains' => ['localhost']]]]
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
            ['instances' => ['default' => ['domains' => ['localhost']]]]
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
