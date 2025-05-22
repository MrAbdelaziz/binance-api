<?php

namespace MrAbdelaziz\BinanceApi\Tests\Unit;

use Mockery;
use MrAbdelaziz\BinanceApi\Services\BinanceApiService;
use MrAbdelaziz\BinanceApi\Services\MarketService;
use MrAbdelaziz\BinanceApi\Tests\TestCase;

class MarketServiceTest extends TestCase
{
    protected MarketService $marketService;

    protected BinanceApiService $mockApiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApiService = Mockery::mock(BinanceApiService::class);
        $this->marketService = new MarketService($this->mockApiService);
    }

    public function test_can_initialize_market_service()
    {
        $this->assertInstanceOf(MarketService::class, $this->marketService);
    }

    public function test_get_price_returns_price_data()
    {
        $expectedResponse = [
            'symbol' => 'BTCUSDT',
            'price' => '50000.00',
        ];

        $this->mockApiService
            ->shouldReceive('publicRequest')
            ->with('/ticker/price', ['symbol' => 'BTCUSDT'])
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->marketService->getPrice('BTCUSDT');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_all_prices_returns_array_of_prices()
    {
        $expectedResponse = [
            ['symbol' => 'BTCUSDT', 'price' => '50000.00'],
            ['symbol' => 'ETHUSDT', 'price' => '3000.00'],
        ];

        $this->mockApiService
            ->shouldReceive('publicRequest')
            ->with('/ticker/price')
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->marketService->getAllPrices();

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_24hr_ticker_returns_ticker_data()
    {
        $expectedResponse = [
            'symbol' => 'BTCUSDT',
            'priceChange' => '1000.00',
            'priceChangePercent' => '2.04',
            'weightedAvgPrice' => '49500.00',
            'prevClosePrice' => '49000.00',
            'lastPrice' => '50000.00',
            'volume' => '1000.00',
        ];

        $this->mockApiService
            ->shouldReceive('publicRequest')
            ->with('/ticker/24hr', ['symbol' => 'BTCUSDT'])
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->marketService->get24hrTicker('BTCUSDT');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_order_book_returns_depth_data()
    {
        $expectedResponse = [
            'lastUpdateId' => 123456789,
            'bids' => [
                ['49900.00', '1.00'],
                ['49800.00', '2.00'],
            ],
            'asks' => [
                ['50100.00', '1.50'],
                ['50200.00', '2.50'],
            ],
        ];

        $this->mockApiService
            ->shouldReceive('publicRequest')
            ->with('/depth', [
                'symbol' => 'BTCUSDT',
                'limit' => 100,
            ])
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->marketService->getOrderBook('BTCUSDT');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_klines_returns_candlestick_data()
    {
        $expectedResponse = [
            [
                1499040000000,      // Open time
                '0.01634790',       // Open
                '0.80000000',       // High
                '0.01575800',       // Low
                '0.01577100',       // Close
                '148976.11427815',  // Volume
                1499644799999,      // Close time
                '2434.19055334',    // Quote asset volume
                308,                // Number of trades
                '1756.87402397',    // Taker buy base asset volume
                '28.46694368',      // Taker buy quote asset volume
                '17928899.62484339', // Ignore
            ],
        ];

        $this->mockApiService
            ->shouldReceive('publicRequest')
            ->with('/klines', [
                'symbol' => 'BTCUSDT',
                'interval' => '1h',
            ])
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->marketService->getKlines('BTCUSDT', '1h');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_top_gainers_processes_tickers_correctly()
    {
        $allTickers = [
            ['symbol' => 'BTCUSDT', 'priceChangePercent' => '5.50'],
            ['symbol' => 'ETHUSDT', 'priceChangePercent' => '3.20'],
            ['symbol' => 'ADAUSDT', 'priceChangePercent' => '-2.10'],
            ['symbol' => 'DOGEUSDT', 'priceChangePercent' => '8.90'],
        ];

        $this->mockApiService
            ->shouldReceive('publicRequest')
            ->with('/ticker/24hr')
            ->once()
            ->andReturn($allTickers);

        $result = $this->marketService->getTopGainers(2);

        $this->assertCount(2, $result);
        $this->assertEquals('DOGEUSDT', $result[0]['symbol']); // Highest gainer first
        $this->assertEquals('BTCUSDT', $result[1]['symbol']);  // Second highest
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
