<?php

namespace MrAbdelaziz\BinanceApi\Traits;

trait HasSignature
{
    /**
     * Create HMAC SHA256 signature for Binance API
     */
    protected function createSignature(array $params, string $secret): string
    {
        $queryString = http_build_query($params, '', '&');

        return hash_hmac('sha256', $queryString, $secret);
    }

    /**
     * Create timestamp for API requests
     */
    protected function createTimestamp(): int
    {
        return round(microtime(true) * 1000);
    }

    /**
     * Validate signature parameters
     */
    protected function validateSignatureParams(array $params): bool
    {
        return isset($params['timestamp']) && isset($params['recvWindow']);
    }

    /**
     * Add required signature parameters
     */
    protected function addSignatureParams(array $params, int $recvWindow = 5000): array
    {
        $params['timestamp'] = $this->createTimestamp();
        $params['recvWindow'] = $recvWindow;

        return $params;
    }

    /**
     * Sign request parameters
     */
    protected function signParams(array $params, string $secret): array
    {
        $signature = $this->createSignature($params, $secret);
        $params['signature'] = $signature;

        return $params;
    }
}
