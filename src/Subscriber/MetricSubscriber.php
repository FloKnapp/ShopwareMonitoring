<?php

namespace Moshp\Monitoring\Subscriber;

use Moshp\Monitoring\Services\MetricService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class MetricSubscriber
 * @package Moshp\Monitoring\Subscriber
 */
class MetricSubscriber implements EventSubscriberInterface
{

    /** @var MetricService */
    private $metricService;

    /**
     * MetricSubscriber constructor.
     * @param MetricService $metricService
     */
    public function __construct(MetricService $metricService)
    {
        $this->metricService = $metricService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'requestEvent',
            ResponseEvent::class => 'responseEvent'
        ];
    }

    /**
     * @param RequestEvent $requestEvent
     */
    public function requestEvent(RequestEvent $requestEvent)
    {
        if (!$requestEvent->isMasterRequest()) {
            return;
        }

        $requestEvent->getRequest()->headers->set('x-request-start', microtime(true));
    }

    /**
     * @param ResponseEvent $responseEvent
     */
    public function responseEvent(ResponseEvent $responseEvent)
    {
        if (false === $responseEvent->isMasterRequest()) {
            return;
        }

        $requestTimestamp = $responseEvent->getRequest()->headers->get('x-request-start');

        $requestTime = number_format(microtime(true) - $requestTimestamp, 3);

        $this->metricService->track($requestTime);
    }

}
