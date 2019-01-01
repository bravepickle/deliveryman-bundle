<?php
/**
 * Date: 2018-12-19
 * Time: 23:17
 */

namespace DeliverymanBundle;

use DeliverymanBundle\DependencyInjection\DeliverymanExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DeliverymanBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new DeliverymanExtension());

        $loader = new YamlFileLoader($container,
            new FileLocator(__DIR__ . '/Resources/config')
        );

        $loader->load('config.yaml');
    }
}