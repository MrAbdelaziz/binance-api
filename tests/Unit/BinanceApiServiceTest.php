<?php

namespace MrAbdelaziz\BinanceApi\Tests\Unit;

use MrAbdelaziz\BinanceApi\Services\BinanceApiService;
use MrAbdelaziz\BinanceApi\Tests\TestCase;

class BinanceApiServiceTest extends TestCase
{
    protected BinanceApiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BinanceApiService([
            'api_key' => 'test_key',
            'api_secret' => 'test_secret',
            'base_url' => 'https://testnet.binance.vision',
            'testnet' => true,
        ]);
    }

    public function test_can_initialize_service()
    {
        $this->assertInstanceOf(BinanceApiService::class, $this->service);
    }

    public function test_can_get_config()
    {
        $config = $this->service->getConfig();

        $this->assertIsArray($config);
        $this->assertEquals('test_key', $config['api_key']);
        $this->assertEquals('test_secret', $config['api_secret']);
        $this->assertTrue($config['testnet']);
    }

    public function test_can_get_specific_config_value()
    {
        $apiKey = $this->service->getConfig('api_key');

        $this->assertEquals('test_key', $apiKey);
    }

    public function test_returns_null_for_invalid_config_key()
    {
        $invalidConfig = $this->service->getConfig('invalid_key');

        $this->assertNull($invalidConfig);
    }

    public function test_can_access_service_modules()
    {
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\AccountService::class, $this->service->account());
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\OrderService::class, $this->service->orders());
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\MarketService::class, $this->service->market());
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\PositionService::class, $this->service->positions());
    }
}
