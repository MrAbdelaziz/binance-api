<?php

namespace MrAbdelaziz\BinanceApi\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MrAbdelaziz\BinanceApi\Exceptions\BinanceApiException;
use MrAbdelaziz\BinanceApi\Traits\HasRateLimit;
use MrAbdelaziz\BinanceApi\Traits\HasSignature;

class BinanceApiService
{
    use HasRateLimit, HasSignature;

    protected array $config;

    protected AccountService $accountService;

    protected OrderService $orderService;

    protected MarketService $marketService;

    protected PositionService $positionService;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'api_key' => '',
            'api_secret' => '',
            'base_url' => 'https://api.binance.com',
            'testnet' => false,
            'timeout' => 30,
            'rate_limits' => [
                'requests_per_minute' => 1200,
                'orders_per_second' => 10,
                'orders_per_day' => 200000,
            ],
        ], $config);

        if ($this->config['testnet']) {
            $this->config['base_url'] = 'https://testnet.binance.vision';
        }

        $this->initializeServices();
    }

    protected function initializeServices(): void
    {
        $this->accountService = new AccountService($this);
        $this->orderService = new OrderService($this);
        $this->marketService = new MarketService($this);
        $this->positionService = new PositionService($this);
    }

    public function account(): AccountService
    {
        return $this->accountService;
    }

    public function orders(): OrderService
    {
        return $this->orderService;
    }

    public function market(): MarketService
    {
        return $this->marketService;
    }

    public function positions(): PositionService
    {
        return $this->positionService;
    }

    public function getConfig(?string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Make a public API request (no authentication required)
     */
    public function publicRequest(string $endpoint, array $params = []): array
    {
        $this->checkRateLimit();

        $url = $this->config['base_url'].'/api/v3'.$endpoint;

        try {
            $response = Http::timeout($this->config['timeout'])
                ->get($url, $params);

            if (! $response->successful()) {
                throw new BinanceApiException(
                    'API request failed: '.$response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $data = $response->json();

            if ($this->shouldLogRequests()) {
                Log::info('Binance API Public Request', [
                    'endpoint' => $endpoint,
                    'params' => $params,
                    'response' => $data,
                ]);
            }

            return $data;
        } catch (\Exception $e) {
            if ($this->shouldLogErrors()) {
                Log::error('Binance API Public Request Failed', [
                    'endpoint' => $endpoint,
                    'params' => $params,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($e instanceof BinanceApiException) {
                throw $e;
            }

            throw new BinanceApiException('Request failed: '.$e->getMessage(), 0, null, $e);
        }
    }

    /**
     * Make a signed API request (authentication required)
     */
    public function signedRequest(string $endpoint, array $params = [], string $method = 'GET'): array
    {
        if (empty($this->config['api_key']) || empty($this->config['api_secret'])) {
            throw new BinanceApiException('API key and secret are required for signed requests');
        }

        $this->checkRateLimit();

        $params['timestamp'] = $this->getTimestamp();
        $params['recvWindow'] = $this->config['defaults']['recv_window'] ?? 5000;

        // Create signature
        $signature = $this->createSignature($params, $this->config['api_secret']);
        $params['signature'] = $signature;

        $url = $this->config['base_url'].'/api/v3'.$endpoint;

        $headers = [
            'X-MBX-APIKEY' => $this->config['api_key'],
        ];

        try {
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders($headers);

            if (strtoupper($method) === 'POST') {
                $response = $response->post($url, $params);
            } elseif (strtoupper($method) === 'DELETE') {
                $response = $response->delete($url, $params);
            } else {
                $response = $response->get($url, $params);
            }

            if (! $response->successful()) {
                $errorData = $response->json();
                throw new BinanceApiException(
                    $errorData['msg'] ?? 'API request failed: '.$response->body(),
                    $errorData['code'] ?? $response->status(),
                    $errorData
                );
            }

            $data = $response->json();

            if ($this->shouldLogRequests()) {
                Log::info('Binance API Signed Request', [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'params' => array_except($params, ['signature']), // Don't log signature
                    'response' => $data,
                ]);
            }

            return $data;
        } catch (\Exception $e) {
            if ($this->shouldLogErrors()) {
                Log::error('Binance API Signed Request Failed', [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'params' => array_except($params, ['signature']), // Don't log signature
                    'error' => $e->getMessage(),
                ]);
            }

            if ($e instanceof BinanceApiException) {
                throw $e;
            }

            throw new BinanceApiException('Request failed: '.$e->getMessage(), 0, null, $e);
        }
    }

    /**
     * Get server time for timestamp synchronization
     */
    public function serverTime(): array
    {
        return $this->publicRequest('/time');
    }

    /**
     * Get exchange information
     */
    public function exchangeInfo(): array
    {
        $cacheKey = 'binance_exchange_info';
        $cacheTtl = $this->config['cache']['ttl']['exchange_info'] ?? 3600;

        if ($this->isCacheEnabled()) {
            return Cache::remember($cacheKey, $cacheTtl, function () {
                return $this->publicRequest('/exchangeInfo');
            });
        }

        return $this->publicRequest('/exchangeInfo');
    }

    /**
     * Ping the API
     */
    public function ping(): array
    {
        return $this->publicRequest('/ping');
    }

    /**
     * Get current timestamp synchronized with Binance server
     */
    protected function getTimestamp(): int
    {
        // You might want to cache server time offset for better performance
        return round(microtime(true) * 1000);
    }

    protected function shouldLogRequests(): bool
    {
        return $this->config['logging']['enabled'] ?? false
            && $this->config['logging']['log_requests'] ?? false;
    }

    protected function shouldLogErrors(): bool
    {
        return $this->config['logging']['enabled'] ?? true
            && $this->config['logging']['log_errors'] ?? true;
    }

    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? true;
    }
}
