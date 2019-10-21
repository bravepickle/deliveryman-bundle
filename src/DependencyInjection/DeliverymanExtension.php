<?php
/**
 * Date: 2018-12-20
 * Time: 00:52
 */

namespace DeliverymanBundle\DependencyInjection;


use DeliverymanBundle\DependencyInjection\InstanceBootstrap\AbstractServiceBootstrapper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DeliverymanExtension extends Extension implements ExtensionInterface
{
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

        $loader = new InstanceLoader();
        foreach ($cfgInstances as $name => $params) {
            /** @var AbstractServiceBootstrapper $bootstrapper */
            $bootstrapper = $loader->load($params['type']);
            $bootstrapper->boot($container, $name, $params);
        }

//        $this->addConfigManagers($container, $cfgInstances);
//        $this->addValidators($container, $cfgInstances);
//        $this->addChannels($container, $cfgInstances);
//        $this->addChannelNormalizers($container);
//        $this->addHandlers($container, $cfgInstances);
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

//    /**
//     * @param ContainerBuilder $container
//     * @param array $cfgInstances
//     */
//    protected function addConfigManagers(ContainerBuilder $container, array $cfgInstances): void
//    {
//        foreach ($cfgInstances as $name => $config) {
//            $definition = new ChildDefinition('deliveryman.config_manager.abstract');
//            $definition->setArgument(0, [$config]);
//            $container->setDefinition(self::SVC_CFG_MANAGER_PREFIX . $name, $definition);
//        }
//    }
//
//    /**
//     * @param ContainerBuilder $container
//     * @param array $cfgInstances
//     */
//    protected function addValidators(ContainerBuilder $container, array $cfgInstances): void
//    {
//        foreach ($cfgInstances as $name => $config) {
//            $definition = new ChildDefinition('deliveryman.validator.abstract');
//            $definition->setArgument(0, new Reference(self::SVC_CFG_MANAGER_PREFIX . $name));
//            $container->setDefinition(self::SVC_VALIDATOR_PREFIX . $name, $definition);
//        }
//    }
//
//    /**
//     * @param ContainerBuilder $container
//     * @param array $cfgInstances
//     */
//    protected function addChannels(ContainerBuilder $container, array $cfgInstances): void
//    {
//        foreach ($container->findTaggedServiceIds(self::TAG_CHANNEL) as $id => $tags) {
//            $def = $container->getDefinition($id);
//            $class = $container->getParameterBag()->resolveValue($def->getClass());
//            if (!is_subclass_of($class, ChannelInterface::class)) {
//                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, ChannelInterface::class));
//            }
//
//            foreach ($cfgInstances as $cfgName => $config) {
//                $definition = new ChildDefinition($id);
//                $definition->setArgument(0, new Reference(self::SVC_CFG_MANAGER_PREFIX . $cfgName));
//
//                foreach ($tags as $tag) {
//                    if (!isset($tag['channel'])) {
//                        throw new InvalidArgumentException('Alias for channel tags must be set.');
//                    }
//
//                    $container->setDefinition(self::SVC_CHANNEL_PREFIX . $tag['channel'] . '.' . $cfgName, $definition);
//                }
//            }
//        }
//    }
//
//    /**
//     * @param ContainerBuilder $container
//     * @param array $cfgInstances
//     */
//    protected function addHandlers(ContainerBuilder $container, array $cfgInstances): void
//    {
//        foreach ($container->findTaggedServiceIds(self::TAG_HANDLER) as $id => $tags) {
//            $def = $container->getDefinition($id);
//            $class = $container->getParameterBag()->resolveValue($def->getClass());
//
//            foreach ($tags as $tag) {
//                if (!isset($tag['channel'])) {
//                    throw new InvalidArgumentException('Sender tags for channel must be set.');
//                }
//
//                if (!is_subclass_of($class, BatchRequestHandlerInterface::class)) {
//                    throw new InvalidArgumentException(sprintf(
//                        'Service "%s" must implement interface "%s".',
//                        $id,
//                        BatchRequestHandlerInterface::class
//                    ));
//                }
//
//                foreach ($cfgInstances as $cfgName => $config) {
//                    $definition = new ChildDefinition($id);
//                    $definition->setPublic(true);
//                    $definition->setArgument(0, new Reference(self::SVC_CHANNEL_PREFIX . $tag['channel'] . '.' . $cfgName));
//                    $definition->setArgument(1, new Reference(self::SVC_CFG_MANAGER_PREFIX . $cfgName));
//                    $definition->setArgument(2, new Reference(self::SVC_VALIDATOR_PREFIX . $cfgName));
//
//                    $container->setDefinition(self::SVC_HANDLER_PREFIX . $tag['channel'] . '.' . $cfgName, $definition);
//                }
//            }
//        }
//    }
//
//    /**
//     * Add channel normalizers for normalizer
//     * @param ContainerBuilder $container
//     */
//    protected function addChannelNormalizers(ContainerBuilder $container)
//    {
//        $batchNormalizer = $container->getDefinition(self::SVC_BATCH_REQUEST_NORMALIZER);
//        foreach ($container->findTaggedServiceIds(self::TAG_CHANNEL_NORMALIZER) as $id => $tags) {
//            $def = $container->getDefinition($id);
//            $class = $container->getParameterBag()->resolveValue($def->getClass());
//            if (!is_subclass_of($class, ChannelNormalizerInterface::class)) {
//                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, ChannelNormalizerInterface::class));
//            }
//
//            $definition = new ChildDefinition($id);
//            $definition->setClass($class);
//            $batchNormalizer->addMethodCall('addChannelNormalizer', [$definition]);
//        }
//    }

}