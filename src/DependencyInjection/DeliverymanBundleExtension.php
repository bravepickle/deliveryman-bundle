<?php
/**
 * Date: 2018-12-20
 * Time: 00:52
 */

namespace DeliverymanBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DeliverymanBundleExtension implements ExtensionInterface
{
    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // TODO: use config settings to affect what will be loaded to services. They returned predefined values from config.yaml
        $this->loadServices($container);
    }

    /**
     * @inheritdoc
     */
    public function getNamespace()
    {
        return 'http://www.example.com/schema/deliveryman';
    }

    /**
     * @inheritdoc
     */
    public function getXsdValidationBasePath()
    {
        return false;
    }

    public function getAlias()
    {
        return 'deliveryman';
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    protected function loadServices(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
    }

}