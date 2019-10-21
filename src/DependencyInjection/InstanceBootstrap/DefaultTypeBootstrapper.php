<?php

namespace DeliverymanBundle\DependencyInjection\InstanceBootstrap;


use Deliveryman\Channel\ChannelInterface;
use Deliveryman\Normalizer\ChannelNormalizerInterface;
use Deliveryman\Service\BatchRequestHandlerInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class DefaultTypeBootstrapper extends AbstractServiceBootstrapper
{
    const SVC_CFG_MANAGER_PREFIX = 'deliveryman.config_manager.';
    const SVC_HANDLER_PREFIX = 'deliveryman.handler.';
    const SVC_VALIDATOR_PREFIX = 'deliveryman.validator.';
    const SVC_CHANNEL_PREFIX = 'deliveryman.channel.';
    const SVC_CHANNEL_NORMALIZER_PREFIX = 'deliveryman.channel_normalizer.';
    const SVC_BATCH_REQUEST_NORMALIZER = 'deliveryman.normalizer';
    const TAG_CHANNEL = 'deliveryman.channel';
    const TAG_CHANNEL_NORMALIZER = 'deliveryman.channel_normalizer';
    const TAG_HANDLER = 'deliveryman.handler';

    const TYPE_NAME = 'default';

    /**
     * @return string
     */
    public static function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function boot(ContainerBuilder $container, string $name, array $params): void
    {
        $this->addConfigManagers($container, $name, $params['config'] ?? []);
        $this->addValidators($container, $name);
        $this->addChannels($container, $name);
        $this->addChannelNormalizers($container);
        $this->addHandlers($container, $name);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $name
     * @param array $config
     */
    protected function addConfigManagers(ContainerBuilder $container, string $name, array $config): void
    {
        $definition = new ChildDefinition('deliveryman.config_manager.abstract');
        $definition->setArgument(0, [$config]);
        $container->setDefinition(self::SVC_CFG_MANAGER_PREFIX . $name, $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $name
     */
    protected function addValidators(ContainerBuilder $container, string $name): void
    {
        $definition = new ChildDefinition('deliveryman.validator.abstract');
        $definition->setArgument(0, new Reference(self::SVC_CFG_MANAGER_PREFIX . $name));
        $container->setDefinition(self::SVC_VALIDATOR_PREFIX . $name, $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $name
     */
    protected function addChannels(ContainerBuilder $container, string $name): void
    {
        foreach ($container->findTaggedServiceIds(self::TAG_CHANNEL) as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($def->getClass());
            if (!is_subclass_of($class, ChannelInterface::class)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, ChannelInterface::class));
            }

            $definition = new ChildDefinition($id);
            $definition->setArgument(0, new Reference(self::SVC_CFG_MANAGER_PREFIX . $name));

            foreach ($tags as $tag) {
                if (!isset($tag['channel'])) {
                    throw new InvalidArgumentException('Alias for channel tags must be set.');
                }

                $container->setDefinition(self::SVC_CHANNEL_PREFIX . $tag['channel'] . '.' . $name, $definition);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $name
     */
    protected function addHandlers(ContainerBuilder $container, string $name): void
    {
        foreach ($container->findTaggedServiceIds(self::TAG_HANDLER) as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($def->getClass());

            foreach ($tags as $tag) {
                if (!isset($tag['channel'])) {
                    throw new InvalidArgumentException('Sender tags for channel must be set.');
                }

                if (!is_subclass_of($class, BatchRequestHandlerInterface::class)) {
                    throw new InvalidArgumentException(sprintf(
                        'Service "%s" must implement interface "%s".',
                        $id,
                        BatchRequestHandlerInterface::class
                    ));
                }

                $definition = new ChildDefinition($id);
                $definition->setPublic(true);
                $definition->setArgument(0, new Reference(self::SVC_CHANNEL_PREFIX . $tag['channel'] . '.' . $name));
                $definition->setArgument(1, new Reference(self::SVC_CFG_MANAGER_PREFIX . $name));
                $definition->setArgument(2, new Reference(self::SVC_VALIDATOR_PREFIX . $name));

                $container->setDefinition(self::SVC_HANDLER_PREFIX . $tag['channel'] . '.' . $name, $definition);
            }
        }
    }

    /**
     * Add channel normalizers for normalizer
     * @param ContainerBuilder $container
     */
    protected function addChannelNormalizers(ContainerBuilder $container)
    {
        $batchNormalizer = $container->getDefinition(self::SVC_BATCH_REQUEST_NORMALIZER);
        foreach ($container->findTaggedServiceIds(self::TAG_CHANNEL_NORMALIZER) as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($def->getClass());
            if (!is_subclass_of($class, ChannelNormalizerInterface::class)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, ChannelNormalizerInterface::class));
            }

            $definition = new ChildDefinition($id);
            $definition->setClass($class);
            $batchNormalizer->addMethodCall('addChannelNormalizer', [$definition]);
        }
    }

}