<?php

namespace DeliverymanBundleTest\Service;


use DeliverymanBundle\Contract\BatchInstanceInterface;

class CustomBatchService implements BatchInstanceInterface
{
    public function __construct(array $config = [])
    {
    }
}