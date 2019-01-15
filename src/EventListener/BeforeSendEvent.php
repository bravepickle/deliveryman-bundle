<?php

namespace DeliverymanBundle\EventListener;


use Deliveryman\Entity\BatchRequest;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BeforeSendEvent
 * @package DeliverymanBundle\EventListener
 */
class BeforeSendEvent extends Event
{
    const NAME = 'deliveryman_bundle.before_send';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var BatchRequest
     */
    protected $batchRequest;

    /**
     * BeforeSendEvent constructor.
     * @param string $type
     * @param BatchRequest $request
     */
    public function __construct(string $type, BatchRequest $request)
    {
        $this->setType($type)
            ->setBatchRequest($request);
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
     * @return BeforeSendEvent
     */
    public function setType(string $type): BeforeSendEvent
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return BatchRequest
     */
    public function getBatchRequest(): BatchRequest
    {
        return $this->batchRequest;
    }

    /**
     * @param BatchRequest $batchRequest
     * @return BeforeSendEvent
     */
    public function setBatchRequest(BatchRequest $batchRequest): BeforeSendEvent
    {
        $this->batchRequest = $batchRequest;

        return $this;
    }

}