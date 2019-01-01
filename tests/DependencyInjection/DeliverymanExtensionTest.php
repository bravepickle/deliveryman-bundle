<?php
/**
 * Date: 2018-12-27
 * Time: 16:00
 */

namespace DeliverymanBundleTest\DependencyInjection;

use Deliveryman\Channel\ChannelInterface;
use Deliveryman\Channel\HttpGraphChannel;
use Deliveryman\Service\Sender;
use Deliveryman\Service\SenderInterface;
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

    /**
     * @throws \Exception
     */
    public function testLoad()
    {
        $container = $this->getContainer([
            ['instances' => ['default' => ['domains' => ['localhost']]]]
        ]);

        $this->assertTrue($container->has('deliveryman.sender.http_graph.default'));
        $this->assertInstanceOf(Sender::class, $container->get('deliveryman.sender.http_graph.default'));
        $this->assertInstanceOf(SenderInterface::class, $container->get('deliveryman.sender.http_graph.default'));


        $this->assertTrue($container->has('deliveryman.channel.http_graph.default'));
        $this->assertInstanceOf(HttpGraphChannel::class, $container->get('deliveryman.channel.http_graph.default'));
        $this->assertInstanceOf(ChannelInterface::class, $container->get('deliveryman.channel.http_graph.default'));
        $this->assertTrue($container->hasDefinition('deliveryman.channel.http_graph.default'));
    }

    public function testGetNamespace()
    {
        $extension = new DeliverymanExtension();
        $this->assertEquals('http://www.example.com/schema/deliveryman', $extension->getNamespace());
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
        $container->compile();

        return $container;
    }
}
