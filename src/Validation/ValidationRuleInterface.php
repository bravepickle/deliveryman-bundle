<?php
/**
 * Date: 2018-12-20
 * Time: 01:17
 */

namespace DeliverymanBundle;

use Deliveryman\Entity\BatchRequest;

interface ValidationRuleInterface
{
    // TODO: use me, test me

    public function validate(BatchRequest $batchRequest): bool;
}