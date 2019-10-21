<?php

namespace DeliverymanBundleTest\Service;


use DeliverymanBundle\DependencyInjection\InstanceBootstrap\ServiceBootstrapperInterface;

class CustomInstanceService implements ServiceBootstrapperInterface
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var array|null
     */
    public $config;

    public function boot(string $name, array $config): void
    {
        $this->name = $name;
        $this->config = $config;
    }

}