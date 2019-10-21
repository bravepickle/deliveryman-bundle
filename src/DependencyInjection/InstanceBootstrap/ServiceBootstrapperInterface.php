<?php
/**
 * Date: 21.10.19
 * Time: 16:43
 */

namespace DeliverymanBundle\DependencyInjection\InstanceBootstrap;


interface ServiceBootstrapperInterface
{
    /**
     * Boot service on load
     * @param string $name
     * @param array $config
     */
    public function boot(string $name, array $config): void;
}