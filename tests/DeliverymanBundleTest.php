<?php
/**
 * Date: 2018-12-20
 * Time: 02:07
 */

namespace DeliverymanBundleTest;


use Deliveryman\Service\Sender;
use DeliverymanBundle\DeliverymanBundle;
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

        $containerBuilder = new ContainerBuilder();

        $bundle->build($containerBuilder);

        $containerBuilder->compile();

        $this->assertInstanceOf(Sender::class, $containerBuilder->get('deliveryman.sender.http'));
    }
}