<?php

namespace DeliverymanBundle\DependencyInjection;

use DeliverymanBundle\DependencyInjection\InstanceBootstrap\AbstractServiceBootstrapper;
use DeliverymanBundle\DependencyInjection\InstanceBootstrap\DefaultTypeBootstrapper;
use DeliverymanBundle\DependencyInjection\InstanceBootstrap\ServiceTypeBootstrapper;

/**
 * Class InstanceLoader
 * @package DeliverymanBundle\DependencyInjection
 */
class InstanceLoader
{
    /**
     * @var AbstractServiceBootstrapper[]
     */
    protected $loaded = [];

    protected function isLoaded($type)
    {
        return isset($this->loaded[$type]);
    }

    public function load(string $type)
    {
        if (!$this->isLoaded($type)) {
            switch ($type) {
                case ServiceTypeBootstrapper::TYPE_NAME:
                    $this->loaded[$type] = new ServiceTypeBootstrapper();
                    break;
                case DefaultTypeBootstrapper::TYPE_NAME:
                default:
                    $this->loaded[$type] = new DefaultTypeBootstrapper();
            }
        }

        return $this->loaded[$type];
    }
}