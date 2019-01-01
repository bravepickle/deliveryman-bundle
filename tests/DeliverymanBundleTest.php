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

        $containerBuilder->compile();

        $this->assertTrue($containerBuilder->has('deliveryman.sender.http_graph.default'));
        $this->assertTrue($containerBuilder->has('deliveryman.channel.http_graph.default'));
    }
}