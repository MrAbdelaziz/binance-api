<?php

namespace MrAbdelaziz\BinanceApi\Tests\Feature;

use MrAbdelaziz\BinanceApi\Facades\BinanceApi;
use MrAbdelaziz\BinanceApi\Services\BinanceApiService;
use MrAbdelaziz\BinanceApi\Tests\TestCase;

class BinanceApiFacadeTest extends TestCase
{
    public function test_facade_resolves_to_service()
    {
        $service = BinanceApi::getFacadeRoot();

        $this->assertInstanceOf(BinanceApiService::class, $service);
    }

    public function test_facade_can_access_service_methods()
    {
        // Test that facade methods are accessible by checking the underlying service
        $service = BinanceApi::getFacadeRoot();
        
        $this->assertTrue(method_exists($service, 'account'));
        $this->assertTrue(method_exists($service, 'orders'));
        $this->assertTrue(method_exists($service, 'market'));
        $this->assertTrue(method_exists($service, 'positions'));
    }

    public function test_facade_returns_correct_service_instances()
    {
        $accountService = BinanceApi::account();
        $orderService = BinanceApi::orders();
        $marketService = BinanceApi::market();
        $positionService = BinanceApi::positions();

        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\AccountService::class, $accountService);
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\OrderService::class, $orderService);
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\MarketService::class, $marketService);
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\PositionService::class, $positionService);
    }
}
