<?php

namespace MrAbdelaziz\BinanceApi\Traits;

use Illuminate\Support\Facades\Cache;
use MrAbdelaziz\BinanceApi\Exceptions\RateLimitException;

trait HasRateLimit
{
    protected array $rateLimitCounters = [];

    /**
     * Check if request is within rate limits
     */
    protected function checkRateLimit(): void
    {
        $this->checkRequestsPerMinute();
        $this->checkOrdersPerSecond();
        $this->updateRateLimitCounters();
    }

    /**
     * Check requests per minute limit
     */
    protected function checkRequestsPerMinute(): void
    {
        $limit = $this->config['rate_limits']['requests_per_minute'] ?? 1200;
        $key = 'binance_requests_per_minute';
        $window = 60; // seconds
        
        $current = Cache::get($key, 0);
        
        if ($current >= $limit) {
            throw new RateLimitException("Requests per minute limit exceeded: {$current}/{$limit}");
        }
    }

    /**
     * Check orders per second limit
     */
    protected function checkOrdersPerSecond(): void
    {
        $limit = $this->config['rate_limits']['orders_per_second'] ?? 10;
        $key = 'binance_orders_per_second';
        $window = 1; // second
        
        $current = Cache::get($key, 0);
        
        if ($current >= $limit) {
            throw new RateLimitException("Orders per second limit exceeded: {$current}/{$limit}");
        }
    }

    /**
     * Update rate limit counters
     */
    protected function updateRateLimitCounters(): void
    {
        // Update requests per minute counter
        $requestKey = 'binance_requests_per_minute';
        $requestCount = Cache::get($requestKey, 0);
        Cache::put($requestKey, $requestCount + 1, 60);

        // Update daily order counter
        $dailyKey = 'binance_orders_daily_' . date('Y-m-d');
        $dailyCount = Cache::get($dailyKey, 0);
        Cache::put($dailyKey, $dailyCount + 1, 86400); // 24 hours
    }

    /**
     * Update order-specific rate limits
     */
    protected function updateOrderRateLimit(): void
    {
        // Update orders per second counter
        $orderKey = 'binance_orders_per_second';
        $orderCount = Cache::get($orderKey, 0);
        Cache::put($orderKey, $orderCount + 1, 1);

        // Update orders per day counter
        $dailyLimit = $this->config['rate_limits']['orders_per_day'] ?? 200000;
        $dailyKey = 'binance_orders_daily_' . date('Y-m-d');
        $dailyCount = Cache::get($dailyKey, 0);
        
        if ($dailyCount >= $dailyLimit) {
            throw new RateLimitException("Daily order limit exceeded: {$dailyCount}/{$dailyLimit}");
        }
        
        Cache::put($dailyKey, $dailyCount + 1, 86400);
    }

    /**
     * Get current rate limit status
     */
    public function getRateLimitStatus(): array
    {
        $requestsPerMinute = Cache::get('binance_requests_per_minute', 0);
        $ordersPerSecond = Cache::get('binance_orders_per_second', 0);
        $ordersToday = Cache::get('binance_orders_daily_' . date('Y-m-d'), 0);

        $limits = $this->config['rate_limits'];

        return [
            'requests_per_minute' => [
                'current' => $requestsPerMinute,
                'limit' => $limits['requests_per_minute'] ?? 1200,
                'percentage' => ($requestsPerMinute / ($limits['requests_per_minute'] ?? 1200)) * 100,
            ],
            'orders_per_second' => [
                'current' => $ordersPerSecond,
                'limit' => $limits['orders_per_second'] ?? 10,
                'percentage' => ($ordersPerSecond / ($limits['orders_per_second'] ?? 10)) * 100,
            ],
            'orders_per_day' => [
                'current' => $ordersToday,
                'limit' => $limits['orders_per_day'] ?? 200000,
                'percentage' => ($ordersToday / ($limits['orders_per_day'] ?? 200000)) * 100,
            ],
        ];
    }

    /**
     * Reset rate limit counters (for testing)
     */
    public function resetRateLimits(): void
    {
        Cache::forget('binance_requests_per_minute');
        Cache::forget('binance_orders_per_second');
        Cache::forget('binance_orders_daily_' . date('Y-m-d'));
    }

    /**
     * Wait if rate limit is approaching
     */
    protected function throttleIfNeeded(): void
    {
        $status = $this->getRateLimitStatus();
        
        // If requests per minute is above 90%, wait a bit
        if ($status['requests_per_minute']['percentage'] > 90) {
            usleep(500000); // 0.5 seconds
        }
        
        // If orders per second is above 80%, wait
        if ($status['orders_per_second']['percentage'] > 80) {
            sleep(1);
        }
    }
}
