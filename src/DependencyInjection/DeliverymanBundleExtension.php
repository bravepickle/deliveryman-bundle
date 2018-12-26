<?php
/**
 * Date: 2018-12-20
 * Time: 00:52
 */

namespace DeliverymanBundle\DependencyInjection;


use Deliveryman\Channel\HttpGraphChannel;
use Deliveryman\Channel\HttpQueueChannel;
use Deliveryman\Service\ConfigManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class DeliverymanBundleExtension implements ExtensionInterface
{
    const TAG_CONFIG_MANAGER_PREFIX = 'deliveryman.config_manager.';
    const TAG_CHANNEL_PREFIX = 'deliveryman.channel.';

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // TODO: use config settings to affect what will be loaded to services. They returned predefined values from config.yaml
        $this->loadServices($container);

        $configs = array_column($configs, 'instances'); // not interested in top level config params
        $cfgByGroup = [];
        foreach ($configs as $configSet) {
            foreach ($configSet as $name => $config) {
                $cfgByGroup[$name][] = $config;
            }
        }

        $this->addConfigManagers($container, $cfgByGroup);
        $this->addChannels($container, $cfgByGroup);

        /** @var ConfigManager $configManager */
//        $configManager = $container->get('deliveryman.config_manager');
//        foreach ($configs as $config) {
//            $configManager->addConfiguration($config);
//        }
//        echo '<pre>';
//        print_r($configs);
//        print_r($container->get('deliveryman.config_manager.default')->getConfiguration());
////        print_r($configManager->getConfiguration());
//        die("\n" . __METHOD__ . ":" . __FILE__ . ":" . __LINE__ . "\n");
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

    /**
     * @param ContainerBuilder $container
     * @param array $cfgByGroup
     */
    protected function addConfigManagers(ContainerBuilder $container, array $cfgByGroup): void
    {
        foreach ($cfgByGroup as $name => $cfgs) {
            $definition = new ChildDefinition('deliveryman.config_manager.abstract');
            $definition->setArgument(0, $cfgs);

            $definition->setPublic(true); // todo: for debug only

            $container->setDefinition(self::TAG_CONFIG_MANAGER_PREFIX . $name, $definition);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array $cfgByGroup
     */
    protected function addChannels(ContainerBuilder $container, array $cfgByGroup): void
    {
        // TODO: use tags for reading additional channels
        $defaultChannels = [
            HttpGraphChannel::NAME => HttpGraphChannel::class,
//            HttpQueueChannel::NAME => HttpQueueChannel::class,
        ];
        foreach ($cfgByGroup as $cfgName => $cfgs) {
//            foreach ( as $item) {
//
//            }
            foreach ($defaultChannels as $name => $class) {

                $definition = new ChildDefinition('deliveryman.channel.abstract');
                $definition->setClass($class);
                $definition->setAutowired(true);
                $definition->setProperty('configManager', new Reference(self::TAG_CONFIG_MANAGER_PREFIX . $cfgName));
                $definition->addTag('deliveryman.channel', ['name' => $name]);

                $container->setDefinition(self::TAG_CHANNEL_PREFIX . $cfgName . '.' . $name, $definition);
            }
        }
    }

}