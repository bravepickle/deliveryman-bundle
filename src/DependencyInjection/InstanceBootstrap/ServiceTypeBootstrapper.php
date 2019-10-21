<?php

namespace DeliverymanBundle\DependencyInjection\InstanceBootstrap;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class ServiceTypeBootstrapper service init
 * @package DeliverymanBundle\DependencyInjection\InstanceBootstrap
 */
class ServiceTypeBootstrapper extends AbstractServiceBootstrapper
{
    const TYPE_NAME = 'service';
    const SVC_PREFIX = 'deliveryman.service.';

    /**
     * @return string
     */
    public static function getName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $name
     * @param array $params
     */
    public function boot(ContainerBuilder $container, string $name, array $params): void
    {
        if (empty($params['service'])) {
            throw new InvalidArgumentException('Service ID is not defined for instance: ' . $name);
        }

        if (!$container->hasDefinition($params['service'])) {
            throw new InvalidArgumentException('Cannot bootstrap unknown service: ' . $params['service']);
        }

        $def = $container->getDefinition($params['service']);

        $class = $def->getClass();

        if (!is_subclass_of($class, ServiceBootstrapperInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Class "%s" must implement interface of "%s" to instantiate batch: %s',
                $class,
                ServiceBootstrapperInterface::class,
                $name
            ));
        }

        $def->addMethodCall('boot', [$name, $params['config'] ?? []]);

        $alias = $container->setAlias(self::SVC_PREFIX . $name, $params['service']);
        $alias->setPublic(true);
    }

}