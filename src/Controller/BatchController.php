<?php

namespace DeliverymanBundle\Controller;

use Deliveryman\Channel\HttpGraphChannel;
use Deliveryman\Entity\BatchRequest;
use Deliveryman\Entity\BatchResponse;
use Deliveryman\Normalizer\HttpGraphChannelNormalizer;
use Deliveryman\Service\BatchRequestHandlerInterface;
use DeliverymanBundle\EventListener\AfterSendEvent;
use DeliverymanBundle\EventListener\BeforeSendEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Class BatchController
 * Handle batch requests
 * @package DeliverymanBundle\Controller
 */
class BatchController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Name of instance from configuration to use
     * @var string
     */
    protected $configName = 'default';

    /**
     * @param string $configName
     */
    public function setConfigName(string $configName): void
    {
        $this->configName = $configName;
    }

    /**
     * Return name of config to check for sender
     * @return string
     */
    public function getConfigName()
    {
        return $this->configName;
    }

    /**
     * @param Request $request
     * @param EventDispatcherInterface|EventDispatcher $dispatcher
     * @return Response
     */
    public function httpGraph(
        Request $request,
        EventDispatcherInterface $dispatcher
    ): Response
    {
        $channel = HttpGraphChannel::NAME;
        $context = [HttpGraphChannelNormalizer::CONTEXT_CHANNEL => $channel];
        $format = JsonEncoder::FORMAT;
        $serializer = $this->container->get('serializer');

        /** @var BatchRequest $batchRequest */
        $batchRequest = $serializer->deserialize($request->getContent(), BatchRequest::class, $format, $context);

        $beforeEvent = new BeforeSendEvent($channel, $batchRequest);
        $dispatcher->dispatch(BeforeSendEvent::NAME, $beforeEvent);
        $batchRequest = $beforeEvent->getBatchRequest();

        /** @var BatchRequestHandlerInterface $handler */
        $handler = $this->container->get('deliveryman.handler.' . $channel . '.' . $this->getConfigName());

        $bus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                BatchRequest::class => [$this->getConfigName() => $handler],
            ])),
        ]);

        $dispatchedMessage = $bus->dispatch($batchRequest);

        /** @var HandledStamp $handledStamp */
        $handledStamp = $dispatchedMessage->last(HandledStamp::class);

        /** @var BatchResponse $batchResponse */
        $batchResponse = $handledStamp->getResult();

        $afterEvent = new AfterSendEvent($channel, $batchResponse);
        $dispatcher->dispatch(AfterSendEvent::NAME, $afterEvent);
        $batchResponse = $afterEvent->getBatchResponse();

        return new Response($serializer->serialize($batchResponse, $format, $context));
    }
}