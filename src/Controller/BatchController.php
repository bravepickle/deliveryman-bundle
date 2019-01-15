<?php

namespace DeliverymanBundle\Controller;

use Deliveryman\Channel\HttpGraphChannel;
use Deliveryman\Entity\BatchRequest;
use Deliveryman\Normalizer\HttpGraphChannelNormalizer;
use Deliveryman\Service\SenderInterface;
use DeliverymanBundle\EventListener\AfterSendEvent;
use DeliverymanBundle\EventListener\BeforeSendEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * Return name of config to check for sender
     * @return string
     */
    protected function getConfigName()
    {
        return 'default';
    }

    /**
     * @param Request $request
     * @param EventDispatcherInterface $dispatcher
     * @return Response
     * @throws \Deliveryman\Exception\SendingException
     * @throws \Psr\Cache\InvalidArgumentException
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

        /** @var SenderInterface $sender */
        $sender = $this->container->get('deliveryman.sender.' . $channel . '.' . $this->getConfigName());
        $batchResponse = $sender->send($batchRequest);

        $afterEvent = new AfterSendEvent($channel, $batchResponse);
        $dispatcher->dispatch(AfterSendEvent::NAME, $afterEvent);
        $batchResponse = $afterEvent->getBatchResponse();

        return new Response($serializer->serialize($batchResponse, $format, $context));
    }
}