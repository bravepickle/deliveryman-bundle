<?php

namespace DeliverymanBundle\EventListener;


use Deliveryman\Entity\BatchResponse;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AfterSendEvent
 * @package DeliverymanBundle\EventListener
 */
class AfterSendEvent extends Event
{
    const NAME = 'deliveryman_bundle.after_send';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var BatchResponse
     */
    protected $batchResponse;

    /**
     * AfterSendEvent constructor.
     * @param string $type
     * @param BatchResponse $batchResponse
     */
    public function __construct(string $type, BatchResponse $batchResponse)
    {
        $this->setType($type)
            ->setBatchResponse($batchResponse)
        ;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return AfterSendEvent
     */
    public function setType(string $type): AfterSendEvent
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return BatchResponse
     */
    public function getBatchResponse(): BatchResponse
    {
        return $this->batchResponse;
    }

    /**
     * @param BatchResponse $batchResponse
     * @return AfterSendEvent
     */
    public function setBatchResponse(BatchResponse $batchResponse): AfterSendEvent
    {
        $this->batchResponse = $batchResponse;

        return $this;
    }

}