<?php
/**
 * Date: 2018-12-27
 * Time: 16:00
 */

namespace DeliverymanBundleTest\DependencyInjection;

use Deliveryman\Channel\HttpGraphChannel;
use DeliverymanBundle\DependencyInjection\DeliverymanExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeliverymanExtensionTest extends TestCase
{

    public function testGetXsdValidationBasePath()
    {
        $extension = new DeliverymanExtension();
        $this->assertFalse($extension->getXsdValidationBasePath());
    }

    public function testGetAlias()
    {
        $extension = new DeliverymanExtension();
        $this->assertEquals('deliveryman', $extension->getAlias());
    }

    public function testLoad()
    {
        $container = $this->getContainer([
            ['instances' => ['default' => ['domains' => ['localhost']]]]
        ]);

//        $this->assertTrue($container->getExtensions());
//        $this->assertTrue($container->hasExtension('deliveryman'));
//        $this->assertTrue($container->findDefinition('deliveryman.sender.abstract'));
        $this->assertTrue($container->getServiceIds());
//        $this->assertTrue($container->findDefinition('deliveryman.validator.default'));
//        $this->assertTrue($container->hasDefinition('deliveryman.validator.default'));
//        $this->assertTrue($container->get('deliveryman.validator.default'));

        $this->assertTrue($container->has('deliveryman.channel.http_graph.default'));
        $this->assertInstanceOf(HttpGraphChannel::class, $container->get('deliveryman.channel.http_graph.default'));
        $this->assertTrue($container->hasDefinition('deliveryman.channel.http_graph.default'));
//        $this->assertTrue($container->getDefinition('deliveryman.channel.default.http_graph'));
    }

    public function testGetNamespace()
    {
        $extension = new DeliverymanExtension();
        $this->assertEquals('http://www.example.com/schema/deliveryman', $extension->getNamespace());
    }

    protected function getContainer(array $configs = [], array $thirdPartyDefinitions = [])
    {
        $container = new ContainerBuilder();
        foreach ($thirdPartyDefinitions as $id => $definition) {
            $container->setDefinition($id, $definition);
        }

//        $container->getCompilerPassConfig()->setOptimizationPasses(array());
//        $container->getCompilerPassConfig()->setRemovingPasses(array());
//        $container->addCompilerPass(new LoggerChannelPass());

        $loader = new DeliverymanExtension();
        $loader->load($configs, $container);
//        $container->registerExtension($loader);
//
//        $container->loadFromExtension($loader->getAlias(), $configs);
        $container->compile();

        return $container;
    }
}
