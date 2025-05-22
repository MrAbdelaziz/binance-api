<?php

namespace MrAbdelaziz\BinanceApi\Tests\Unit;

use MrAbdelaziz\BinanceApi\Services\OrderService;
use MrAbdelaziz\BinanceApi\Services\BinanceApiService;
use MrAbdelaziz\BinanceApi\Exceptions\BinanceApiException;
use MrAbdelaziz\BinanceApi\Tests\TestCase;
use Mockery;

class OrderServiceTest extends TestCase
{
    protected OrderService $orderService;
    protected BinanceApiService $mockApiService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockApiService = Mockery::mock(BinanceApiService::class);
        $this->orderService = new OrderService($this->mockApiService);
    }

    public function test_can_initialize_order_service()
    {
        $this->assertInstanceOf(OrderService::class, $this->orderService);
    }

    public function test_market_buy_creates_correct_order_data()
    {
        $expectedOrderData = [
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'MARKET',
            'quantity' => '0.00100000',
        ];

        $expectedResponse = [
            'orderId' => 12345,
            'status' => 'FILLED',
            'executedQty' => '0.001'
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/order', $expectedOrderData, 'POST')
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->orderService->marketBuy('BTCUSDT', 0.001);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_limit_buy_creates_correct_order_data()
    {
        $expectedOrderData = [
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'LIMIT',
            'timeInForce' => 'GTC',
            'quantity' => '0.00100000',
            'price' => '50000.00000000',
        ];

        $expectedResponse = [
            'orderId' => 12345,
            'status' => 'NEW'
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/order', $expectedOrderData, 'POST')
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->orderService->limitBuy('BTCUSDT', 0.001, 50000);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_cancel_order_with_order_id()
    {
        $expectedParams = [
            'symbol' => 'BTCUSDT',
            'orderId' => 12345
        ];

        $expectedResponse = [
            'orderId' => 12345,
            'status' => 'CANCELED'
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/order', $expectedParams, 'DELETE')
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->orderService->cancelOrder('BTCUSDT', 12345);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_cancel_order_throws_exception_without_identifier()
    {
        $this->expectException(BinanceApiException::class);
        $this->expectExceptionMessage('Either orderId or origClientOrderId must be provided');

        $this->orderService->cancelOrder('BTCUSDT');
    }

    public function test_get_order_with_order_id()
    {
        $expectedParams = [
            'symbol' => 'BTCUSDT',
            'orderId' => 12345
        ];

        $expectedResponse = [
            'orderId' => 12345,
            'status' => 'FILLED',
            'executedQty' => '0.001'
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/order', $expectedParams)
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->orderService->getOrder('BTCUSDT', 12345);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_open_orders_all_symbols()
    {
        $expectedResponse = [
            ['orderId' => 1, 'symbol' => 'BTCUSDT'],
            ['orderId' => 2, 'symbol' => 'ETHUSDT']
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/openOrders', [])
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->orderService->getOpenOrders();

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_open_orders_specific_symbol()
    {
        $expectedParams = ['symbol' => 'BTCUSDT'];
        $expectedResponse = [
            ['orderId' => 1, 'symbol' => 'BTCUSDT']
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/openOrders', $expectedParams)
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->orderService->getOpenOrders('BTCUSDT');

        $this->assertEquals($expectedResponse, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
