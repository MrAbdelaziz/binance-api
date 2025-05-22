<?php

namespace MrAbdelaziz\BinanceApi\Services;

class MarketService
{
    protected BinanceApiService $api;

    public function __construct(BinanceApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Get current price for a symbol
     */
    public function getPrice(string $symbol): array
    {
        return $this->api->publicRequest('/ticker/price', [
            'symbol' => strtoupper($symbol),
        ]);
    }

    /**
     * Get current prices for all symbols
     */
    public function getAllPrices(): array
    {
        return $this->api->publicRequest('/ticker/price');
    }

    /**
     * Get 24hr ticker statistics for a symbol
     */
    public function get24hrTicker(string $symbol): array
    {
        return $this->api->publicRequest('/ticker/24hr', [
            'symbol' => strtoupper($symbol),
        ]);
    }

    /**
     * Get 24hr ticker statistics for all symbols
     */
    public function getAll24hrTickers(): array
    {
        return $this->api->publicRequest('/ticker/24hr');
    }

    /**
     * Get price change statistics
     */
    public function getPriceChangeStats(?string $symbol = null): array
    {
        $params = [];
        if ($symbol) {
            $params['symbol'] = strtoupper($symbol);
        }

        return $this->api->publicRequest('/ticker/24hr', $params);
    }

    /**
     * Get symbol order book ticker (best price/qty on the order book)
     */
    public function getBookTicker(?string $symbol = null): array
    {
        $params = [];
        if ($symbol) {
            $params['symbol'] = strtoupper($symbol);
        }

        return $this->api->publicRequest('/ticker/bookTicker', $params);
    }

    /**
     * Get order book for a symbol
     */
    public function getOrderBook(string $symbol, int $limit = 100): array
    {
        return $this->api->publicRequest('/depth', [
            'symbol' => strtoupper($symbol),
            'limit' => $limit,
        ]);
    }

    /**
     * Get recent trades for a symbol
     */
    public function getRecentTrades(string $symbol, int $limit = 500): array
    {
        return $this->api->publicRequest('/trades', [
            'symbol' => strtoupper($symbol),
            'limit' => $limit,
        ]);
    }

    /**
     * Get historical trades for a symbol
     */
    public function getHistoricalTrades(string $symbol, int $limit = 500, ?int $fromId = null): array
    {
        $params = [
            'symbol' => strtoupper($symbol),
            'limit' => $limit,
        ];

        if ($fromId !== null) {
            $params['fromId'] = $fromId;
        }

        return $this->api->publicRequest('/historicalTrades', $params);
    }

    /**
     * Get compressed/aggregate trades for a symbol
     */
    public function getAggregateTrades(string $symbol, array $options = []): array
    {
        $params = array_merge([
            'symbol' => strtoupper($symbol),
        ], $options);

        // Optional parameters: fromId, startTime, endTime, limit
        return $this->api->publicRequest('/aggTrades', $params);
    }

    /**
     * Get klines/candlestick data for a symbol
     */
    public function getKlines(string $symbol, string $interval, array $options = []): array
    {
        $params = array_merge([
            'symbol' => strtoupper($symbol),
            'interval' => $interval,
        ], $options);

        // Optional parameters: startTime, endTime, limit
        return $this->api->publicRequest('/klines', $params);
    }

    /**
     * Get current average price for a symbol
     */
    public function getAveragePrice(string $symbol): array
    {
        return $this->api->publicRequest('/avgPrice', [
            'symbol' => strtoupper($symbol),
        ]);
    }

    /**
     * Get exchange information
     */
    public function getExchangeInfo(): array
    {
        return $this->api->exchangeInfo();
    }

    /**
     * Get server time
     */
    public function getServerTime(): array
    {
        return $this->api->serverTime();
    }

    /**
     * Ping the API
     */
    public function ping(): array
    {
        return $this->api->ping();
    }

    /**
     * Get all USDT trading pairs
     */
    public function getUsdtPairs(): array
    {
        $exchangeInfo = $this->getExchangeInfo();

        return array_filter($exchangeInfo['symbols'], function ($symbol) {
            return $symbol['quoteAsset'] === 'USDT' && $symbol['status'] === 'TRADING';
        });
    }

    /**
     * Get market cap ranking (approximate using volume * price)
     */
    public function getMarketCapRanking(int $limit = 100): array
    {
        $tickers = $this->getAll24hrTickers();

        // Calculate approximate market cap using volume * price
        $marketCaps = array_map(function ($ticker) {
            $volume = (float) $ticker['volume'];
            $price = (float) $ticker['lastPrice'];

            return [
                'symbol' => $ticker['symbol'],
                'price' => $price,
                'volume' => $volume,
                'marketCap' => $volume * $price,
                'priceChange' => $ticker['priceChangePercent'],
            ];
        }, $tickers);

        // Sort by market cap descending
        usort($marketCaps, function ($a, $b) {
            return $b['marketCap'] <=> $a['marketCap'];
        });

        return array_slice($marketCaps, 0, $limit);
    }

    /**
     * Get top gainers
     */
    public function getTopGainers(int $limit = 20): array
    {
        $tickers = $this->getAll24hrTickers();

        // Filter and sort by price change percentage
        $gainers = array_filter($tickers, function ($ticker) {
            return (float) $ticker['priceChangePercent'] > 0;
        });

        usort($gainers, function ($a, $b) {
            return (float) $b['priceChangePercent'] <=> (float) $a['priceChangePercent'];
        });

        return array_slice($gainers, 0, $limit);
    }

    /**
     * Get top losers
     */
    public function getTopLosers(int $limit = 20): array
    {
        $tickers = $this->getAll24hrTickers();

        // Filter and sort by price change percentage
        $losers = array_filter($tickers, function ($ticker) {
            return (float) $ticker['priceChangePercent'] < 0;
        });

        usort($losers, function ($a, $b) {
            return (float) $a['priceChangePercent'] <=> (float) $b['priceChangePercent'];
        });

        return array_slice($losers, 0, $limit);
    }

    /**
     * Get most active symbols by volume
     */
    public function getMostActive(int $limit = 20): array
    {
        $tickers = $this->getAll24hrTickers();

        // Sort by quote volume (USDT volume)
        usort($tickers, function ($a, $b) {
            return (float) $b['quoteVolume'] <=> (float) $a['quoteVolume'];
        });

        return array_slice($tickers, 0, $limit);
    }
}
