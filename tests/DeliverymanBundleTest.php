<?php
/**
 * Date: 2018-12-20
 * Time: 02:07
 */

namespace DeliverymanBundleTest;


use DeliverymanBundle\DeliverymanBundle;
use DeliverymanBundle\DependencyInjection\DeliverymanExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class DeliverymanBundleTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testBuild()
    {
        $bundle = new DeliverymanBundle();
        $extension = $bundle->getContainerExtension();
        $this->assertInstanceOf(DeliverymanExtension::class, $extension);

        $containerBuilder = new ContainerBuilder();
        $bundle->build($containerBuilder);
        $containerBuilder->loadFromExtension('deliveryman');

        $this->addSerializer($containerBuilder);

        $containerBuilder->compile();

        $this->assertTrue($containerBuilder->has('deliveryman.sender.http_graph.default'));
        $this->assertTrue($containerBuilder->has('deliveryman.channel.http_graph.default'));
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