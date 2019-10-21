<?php

namespace DeliverymanBundle\DependencyInjection\InstanceBootstrap;


use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractServiceBootstrapper
{
    /**
     * Get type name of given bootstrapper
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * Initialize data for given type of bootstrapper
     * @param ContainerBuilder $container
     * @param string $name
     * @param array $params
     */
    abstract public function boot(ContainerBuilder $container, string $name, array $params): void;
}