<?php

namespace MrAbdelaziz\BinanceApi\Services;

use MrAbdelaziz\BinanceApi\Exceptions\BinanceApiException;

class OrderService
{
    protected BinanceApiService $api;

    public function __construct(BinanceApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Place a new order
     */
    public function newOrder(array $orderData): array
    {
        $requiredFields = ['symbol', 'side', 'type', 'quantity'];
        foreach ($requiredFields as $field) {
            if (!isset($orderData[$field])) {
                throw new BinanceApiException("Required field '{$field}' is missing");
            }
        }

        $orderData['symbol'] = strtoupper($orderData['symbol']);
        $orderData['side'] = strtoupper($orderData['side']);
        $orderData['type'] = strtoupper($orderData['type']);

        return $this->api->signedRequest('/order', $orderData, 'POST');
    }

    /**
     * Place a market buy order
     */
    public function marketBuy(string $symbol, float $quantity, array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => 'BUY',
            'type' => 'MARKET',
            'quantity' => number_format($quantity, 8, '.', ''),
        ], $options);

        return $this->newOrder($orderData);
    }

    /**
     * Place a market sell order
     */
    public function marketSell(string $symbol, float $quantity, array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => 'SELL',
            'type' => 'MARKET',
            'quantity' => number_format($quantity, 8, '.', ''),
        ], $options);

        return $this->newOrder($orderData);
    }

    /**
     * Place a limit buy order
     */
    public function limitBuy(string $symbol, float $quantity, float $price, array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => 'BUY',
            'type' => 'LIMIT',
            'timeInForce' => 'GTC',
            'quantity' => number_format($quantity, 8, '.', ''),
            'price' => number_format($price, 8, '.', ''),
        ], $options);

        return $this->newOrder($orderData);
    }

    /**
     * Place a limit sell order
     */
    public function limitSell(string $symbol, float $quantity, float $price, array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => 'SELL',
            'type' => 'LIMIT',
            'timeInForce' => 'GTC',
            'quantity' => number_format($quantity, 8, '.', ''),
            'price' => number_format($price, 8, '.', ''),
        ], $options);

        return $this->newOrder($orderData);
    }

    /**
     * Place a stop-loss order
     */
    public function stopLoss(string $symbol, float $quantity, float $stopPrice, string $side = 'SELL', array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => strtoupper($side),
            'type' => 'STOP_LOSS',
            'quantity' => number_format($quantity, 8, '.', ''),
            'stopPrice' => number_format($stopPrice, 8, '.', ''),
        ], $options);

        return $this->newOrder($orderData);
    }

    /**
     * Place a stop-loss limit order
     */
    public function stopLossLimit(string $symbol, float $quantity, float $price, float $stopPrice, string $side = 'SELL', array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => strtoupper($side),
            'type' => 'STOP_LOSS_LIMIT',
            'timeInForce' => 'GTC',
            'quantity' => number_format($quantity, 8, '.', ''),
            'price' => number_format($price, 8, '.', ''),
            'stopPrice' => number_format($stopPrice, 8, '.', ''),
        ], $options);

        return $this->newOrder($orderData);
    }

    /**
     * Place a take-profit order
     */
    public function takeProfit(string $symbol, float $quantity, float $stopPrice, string $side = 'SELL', array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => strtoupper($side),
            'type' => 'TAKE_PROFIT',
            'quantity' => number_format($quantity, 8, '.', ''),
            'stopPrice' => number_format($stopPrice, 8, '.', ''),
        ], $options);

        return $this->newOrder($orderData);
    }

    /**
     * Place a take-profit limit order
     */
    public function takeProfitLimit(string $symbol, float $quantity, float $price, float $stopPrice, string $side = 'SELL', array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => strtoupper($side),
            'type' => 'TAKE_PROFIT_LIMIT',
            'timeInForce' => 'GTC',
            'quantity' => number_format($quantity, 8, '.', ''),
            'price' => number_format($price, 8, '.', ''),
            'stopPrice' => number_format($stopPrice, 8, '.', ''),
        ], $options);

        return $this->newOrder($orderData);
    }

    /**
     * Place an OCO (One-Cancels-Other) order
     */
    public function ocoOrder(string $symbol, float $quantity, float $price, float $stopPrice, float $stopLimitPrice, string $side = 'SELL', array $options = []): array
    {
        $orderData = array_merge([
            'symbol' => strtoupper($symbol),
            'side' => strtoupper($side),
            'quantity' => number_format($quantity, 8, '.', ''),
            'price' => number_format($price, 8, '.', ''),
            'stopPrice' => number_format($stopPrice, 8, '.', ''),
            'stopLimitPrice' => number_format($stopLimitPrice, 8, '.', ''),
            'stopLimitTimeInForce' => 'GTC',
        ], $options);

        return $this->api->signedRequest('/order/oco', $orderData, 'POST');
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(string $symbol, int $orderId = null, string $origClientOrderId = null): array
    {
        if ($orderId === null && $origClientOrderId === null) {
            throw new BinanceApiException('Either orderId or origClientOrderId must be provided');
        }

        $params = ['symbol' => strtoupper($symbol)];
        
        if ($orderId !== null) {
            $params['orderId'] = $orderId;
        }
        
        if ($origClientOrderId !== null) {
            $params['origClientOrderId'] = $origClientOrderId;
        }

        return $this->api->signedRequest('/order', $params, 'DELETE');
    }

    /**
     * Cancel an OCO order
     */
    public function cancelOcoOrder(string $symbol, int $orderListId = null, string $listClientOrderId = null): array
    {
        if ($orderListId === null && $listClientOrderId === null) {
            throw new BinanceApiException('Either orderListId or listClientOrderId must be provided');
        }

        $params = ['symbol' => strtoupper($symbol)];
        
        if ($orderListId !== null) {
            $params['orderListId'] = $orderListId;
        }
        
        if ($listClientOrderId !== null) {
            $params['listClientOrderId'] = $listClientOrderId;
        }

        return $this->api->signedRequest('/orderList', $params, 'DELETE');
    }

    /**
     * Cancel all open orders for a symbol
     */
    public function cancelAllOrders(string $symbol): array
    {
        return $this->api->signedRequest('/openOrders', [
            'symbol' => strtoupper($symbol)
        ], 'DELETE');
    }

    /**
     * Get order status
     */
    public function getOrder(string $symbol, int $orderId = null, string $origClientOrderId = null): array
    {
        if ($orderId === null && $origClientOrderId === null) {
            throw new BinanceApiException('Either orderId or origClientOrderId must be provided');
        }

        $params = ['symbol' => strtoupper($symbol)];
        
        if ($orderId !== null) {
            $params['orderId'] = $orderId;
        }
        
        if ($origClientOrderId !== null) {
            $params['origClientOrderId'] = $origClientOrderId;
        }

        return $this->api->signedRequest('/order', $params);
    }

    /**
     * Get all open orders for a symbol or all symbols
     */
    public function getOpenOrders(string $symbol = null): array
    {
        $params = [];
        if ($symbol) {
            $params['symbol'] = strtoupper($symbol);
        }

        return $this->api->signedRequest('/openOrders', $params);
    }

    /**
     * Get all orders for a symbol (active, canceled, or filled)
     */
    public function getAllOrders(string $symbol, array $options = []): array
    {
        $params = array_merge([
            'symbol' => strtoupper($symbol),
        ], $options);

        // Optional parameters: orderId, startTime, endTime, limit
        return $this->api->signedRequest('/allOrders', $params);
    }

    /**
     * Get OCO order status
     */
    public function getOcoOrder(int $orderListId = null, string $origClientOrderId = null): array
    {
        if ($orderListId === null && $origClientOrderId === null) {
            throw new BinanceApiException('Either orderListId or origClientOrderId must be provided');
        }

        $params = [];
        
        if ($orderListId !== null) {
            $params['orderListId'] = $orderListId;
        }
        
        if ($origClientOrderId !== null) {
            $params['origClientOrderId'] = $origClientOrderId;
        }

        return $this->api->signedRequest('/orderList', $params);
    }

    /**
     * Get all OCO orders
     */
    public function getAllOcoOrders(array $options = []): array
    {
        // Optional parameters: fromId, startTime, endTime, limit
        return $this->api->signedRequest('/allOrderList', $options);
    }

    /**
     * Get open OCO orders
     */
    public function getOpenOcoOrders(): array
    {
        return $this->api->signedRequest('/openOrderList');
    }

    /**
     * Test new order creation (validates parameters without placing order)
     */
    public function testNewOrder(array $orderData): array
    {
        $orderData['symbol'] = strtoupper($orderData['symbol']);
        $orderData['side'] = strtoupper($orderData['side']);
        $orderData['type'] = strtoupper($orderData['type']);

        return $this->api->signedRequest('/order/test', $orderData, 'POST');
    }

    /**
     * Get order count usage for the current day
     */
    public function getOrderRateLimit(): array
    {
        return $this->api->signedRequest('/rateLimit/order');
    }
}
