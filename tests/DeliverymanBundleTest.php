<?php
/**
 * Date: 2018-12-20
 * Time: 02:07
 */

namespace DeliverymanBundleTest;


use Deliveryman\Service\Sender;
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
//        $bundle->boot();
//        $bundle->

        $containerBuilder->loadFromExtension('deliveryman');

        $containerBuilder->compile();


//        echo '<pre>';
////        var_dump($containerBuilder->get('deliveryman.config_manager.default')->getConfiguration());
//        var_dump($containerBuilder->get('deliveryman.channel.default.http_graph'));
////        print_r($configManager->getConfiguration());
//        die("\n" . __METHOD__ . ":" . __FILE__ . ":" . __LINE__ . "\n");
        $this->assertTrue($containerBuilder->has('deliveryman.validator.default'));
        $this->assertTrue($containerBuilder->get('deliveryman.validator.default'));
        $this->assertTrue($containerBuilder->has('deliveryman.channel.http_graph.default'));
        $this->assertTrue($containerBuilder->get('deliveryman.channel.http_graph.default'));
//        $this->assertInstanceOf(Sender::class, $containerBuilder->get('deliveryman.sender.http'));
    }
}