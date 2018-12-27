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
use Deliveryman\Service\Sender;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class DeliverymanExtension extends Extension implements ExtensionInterface
{
    const SVC_CFG_MANAGER_PREFIX = 'deliveryman.config_manager.';
    const SVC_SENDER_PREFIX = 'deliveryman.sender.';
    const SVC_VALIDATOR_PREFIX = 'deliveryman.validator.';
    const TAG_CHANNEL = 'deliveryman.channel';
    const TAG_SENDER = 'deliveryman.sender';

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        // TODO: use config settings to affect what will be loaded to services. They returned predefined values from config.yaml
        // TODO: remove config manager as DI for services use compiled array of values instead
        $this->loadServices($container);
        $cfgInstances = $config['instances'] ?? [];

        $this->addConfigManagers($container, $cfgInstances);
        $this->addValidators($container, $cfgInstances);
        $this->addChannels($container, $cfgInstances);
        $this->addSenders($container, $cfgInstances);
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
     * @param array $cfgInstances
     */
    protected function addConfigManagers(ContainerBuilder $container, array $cfgInstances): void
    {
        foreach ($cfgInstances as $name => $config) {
            $definition = new ChildDefinition('deliveryman.config_manager.abstract');
            $definition->setArgument(0, [$config]);
            $container->setDefinition(self::SVC_CFG_MANAGER_PREFIX . $name, $definition);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array $cfgInstances
     */
    protected function addValidators(ContainerBuilder $container, array $cfgInstances): void
    {
        foreach ($cfgInstances as $name => $config) {
            $definition = new ChildDefinition('deliveryman.validator.abstract');
            $definition->setArgument(0, [$config]);
            $container->setDefinition(self::SVC_VALIDATOR_PREFIX . $name, $definition);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array $cfgInstances
     */
    protected function addChannels(ContainerBuilder $container, array $cfgInstances): void
    {
        foreach ($container->findTaggedServiceIds(self::TAG_CHANNEL) as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($def->getClass());
            if (!is_subclass_of($class, ChannelInterface::class)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, ChannelInterface::class));
            }

            foreach ($cfgInstances as $cfgName => $config) {
                $definition = new ChildDefinition($id);
                $definition->setArgument(0, new Reference(self::SVC_CFG_MANAGER_PREFIX . $cfgName));

                foreach ($tags as $tag) {
                    if (!isset($tag['channel'])) {
                        throw new InvalidArgumentException('Alias for channel tags must be set');
                    }

                    $container->setDefinition(self::TAG_CHANNEL . '.' . $tag['channel'] . '.' . $cfgName, $definition);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array $cfgInstances
     */
    protected function addSenders(ContainerBuilder $container, array $cfgInstances): void
    {
        foreach ($container->findTaggedServiceIds(self::TAG_SENDER) as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($def->getClass());

            foreach ($tags as $tag) {
                // TODO: add sender interface instead for abstract classes
                if (!isset($tag['channel'])) {
                    throw new InvalidArgumentException('Sender tags for channel must be set');
                }

                if (!is_subclass_of($class, Sender::class) && $class !== Sender::class) {
                    throw new InvalidArgumentException(sprintf('Service "%s" must instance of class "%s".', $id, Sender::class));
                }

                foreach ($cfgInstances as $cfgName => $config) {
                    $definition = new ChildDefinition($id);
//                $definition->setArgument(0, new Reference(self::SVC_SENDER_PREFIX . $cfgName));


                    $definition->setProperty('channel', new Reference(self::TAG_CHANNEL . '.' . $tag['channel'] . '.' . $cfgName));
                    $container->setDefinition(self::TAG_SENDER . '.' . $tag['channel'] . '.' . $cfgName, $definition);
                }
            }
        }
    }

}