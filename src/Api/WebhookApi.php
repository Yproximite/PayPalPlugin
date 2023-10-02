<?php

declare(strict_types=1);

namespace Sylius\PayPalPlugin\Api;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

final class WebhookApi implements WebhookApiInterface
{
    private ClientInterface $client;

    private string $baseUrl;

    private LoggerInterface $logger;

    public function __construct(ClientInterface $client, string $baseUrl, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
    }

    public function register(string $token, string $webhookUrl): array
    {
        $this->logger->info('[paypal] WebhookApi', ['url' => $webhookUrl, 'baseUrl' => $this->baseUrl]);
        $response = $this->client->request('POST', $this->baseUrl . 'v1/notifications/webhooks', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'url' => preg_replace('/^http:/i', 'https:', $webhookUrl),
                'event_types' => [
                    ['name' => 'PAYMENT.CAPTURE.REFUNDED'],
                ],
            ],
        ]);

        $result = (array) json_decode($response->getBody()->getContents(), true);
        $this->logger->info('[paypal] WebhookApi result', ['content' => $result]);

        return $result;
    }
}
