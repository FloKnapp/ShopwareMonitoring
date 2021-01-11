<?php

namespace Moshp\Monitoring\Services;

use GuzzleHttp\Client;
use Monolog\Logger;

/**
 * Class MetricService
 * @package Moshp\Monitoring\Services
 */
class MetricService
{

    private const METRIC_NAME_EXECUTION_TIME = 'system.application.execution_time';
    private const METRIC_TYPE_DEFAULT = 'gauge';

    private const METRIC_SUCCESS_CODE = 202;

    /** @var Client */
    private $client;

    /** @var Logger */
    private $logger;

    /** @var string */
    private $apiKey = '';

    /** @var string */
    private $apiEndpoint = '';

    /**
     * MetricSubscriber constructor.
     *
     * @param Client $client
     * @param Logger $logger
     * @param string $apiKey
     * @param string $apiEndpoint
     */
    public function __construct(Client $client, Logger $logger, string $apiKey, string $apiEndpoint)
    {
        $this->client      = $client;
        $this->logger      = $logger;
        $this->apiKey      = $apiKey;
        $this->apiEndpoint = $apiEndpoint;
    }

    /**
     * Send a value to NewRelic Metric API
     *
     * @param float  $time
     * @param string $type
     * @return void
     */
    public function track($time = 0.00, $type = self::METRIC_TYPE_DEFAULT): void
    {
        try {

            $body = \GuzzleHttp\json_encode([[
                'metrics' => [
                    [
                        'name'      => self::METRIC_NAME_EXECUTION_TIME,
                        'type'      => $type,
                        'value'     => (float)$time,
                        'timestamp' => time()
                    ]
                ]
            ]]);

            $this->logger->info('Sending json: ' . $body);

            $req = $this->client->request('POST', $this->apiEndpoint, [
                'headers' => [
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'curl/7.65.3'
                ],
                'body' => $body
            ]);

            if (self::METRIC_SUCCESS_CODE !== $req->getStatusCode()) {

                $this->logger->notice('Error while sending metric data to NewRelic.', [
                    'response' => (string)$req->getBody()
                ]);

                throw new \LogicException('Metric push to NewRelic failed, received status code ' . $req->getStatusCode());

            }

            $this->logger->debug('Sending metric data to NewRelic.');

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

    }

}
