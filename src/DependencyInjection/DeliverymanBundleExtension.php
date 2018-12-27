<?php
/**
 * Date: 2018-12-20
 * Time: 00:52
 */

namespace DeliverymanBundle\DependencyInjection;


use Deliveryman\Channel\ChannelInterface;
use Deliveryman\Channel\HttpGraphChannel;
use Deliveryman\Channel\HttpQueueChannel;
use Deliveryman\Service\ConfigManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class DeliverymanBundleExtension implements ExtensionInterface
{
    const TAG_CONFIG_MANAGER_PREFIX = 'deliveryman.config_manager.';
    const TAG_CHANNEL = 'deliveryman.channel';

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
        foreach ($container->findTaggedServiceIds(self::TAG_CHANNEL) as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($def->getClass());
            if (!is_subclass_of($class, ChannelInterface::class)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, ChannelInterface::class));
            }

            foreach ($cfgByGroup as $cfgName => $cfgs) {
                $definition = new ChildDefinition($id);
//                $definition = new ChildDefinition('maker.auto_command.abstract');
//            $definition->setClass(MakerCommand::class);
//                $definition->setClass($class);
//                $definition->replaceArgument(0, new Reference($id));
                $definition->setArgument(0, new Reference(self::TAG_CONFIG_MANAGER_PREFIX . $cfgName));
//                $definition->addTag(self::TAG_CHANNEL, ['class' => $class::NAME]);
//                $definition->addTag(self::TAG_CHANNEL, ['class' => $class::NAME]);

//                var_dump(self::TAG_CHANNEL . '.' . $cfgName . '.' . $id);
//                die("\n" . __METHOD__ . ":" . __FILE__ . ":" . __LINE__ . "\n");

                foreach ($tags as $tag) {
                    if (!isset($tag['alias'])) {
                        throw new InvalidArgumentException('Alias for channel tags must be set');
                    }
                    $container->setDefinition(self::TAG_CHANNEL . '.' . $cfgName . '.' . $tag['alias'], $definition);
                }
            }
        }
//        $defaultChannels = [
//            HttpGraphChannel::NAME => HttpGraphChannel::class,
////            HttpQueueChannel::NAME => HttpQueueChannel::class,
//        ];
//        foreach ($cfgByGroup as $cfgName => $cfgs) {
////            foreach ( as $item) {
////
////            }
//            foreach ($defaultChannels as $name => $class) {
//
//                $definition = new ChildDefinition('deliveryman.channel.abstract');
//                $definition->setClass($class);
//                $definition->setAutowired(true);
//                $definition->setProperty('configManager', new Reference(self::TAG_CONFIG_MANAGER_PREFIX . $cfgName));
//                $definition->addTag(self::TAG_CHANNEL, ['name' => $name]);
//
//                $container->setDefinition(self::TAG_CHANNEL . '.' . $cfgName . '.' . $name, $definition);
//            }
//        }
    }

}