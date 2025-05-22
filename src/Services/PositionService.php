<?php

namespace MrAbdelaziz\BinanceApi\Services;

use Illuminate\Support\Facades\Cache;

class PositionService
{
    protected BinanceApiService $api;

    public function __construct(BinanceApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Get all open positions by analyzing account balances and orders
     */
    public function getOpenPositions(): array
    {
        $account = $this->api->account()->getAccountInfo();
        $balances = $account['balances'] ?? [];

        $positions = [];
        
        foreach ($balances as $balance) {
            $free = (float) $balance['free'];
            $locked = (float) $balance['locked'];
            $total = $free + $locked;

            // Only consider positions with meaningful balances
            if ($total > 0 && $balance['asset'] !== 'USDT') {
                $symbol = $balance['asset'] . 'USDT';
                
                // Get current price for this position
                try {
                    $priceData = $this->api->market()->getPrice($symbol);
                    $currentPrice = (float) $priceData['price'];
                    
                    $positions[] = [
                        'asset' => $balance['asset'],
                        'symbol' => $symbol,
                        'quantity' => $total,
                        'free' => $free,
                        'locked' => $locked,
                        'currentPrice' => $currentPrice,
                        'currentValue' => $total * $currentPrice,
                        'percentage' => 0, // Will be calculated after getting total portfolio value
                    ];
                } catch (\Exception $e) {
                    // Skip assets without USDT pairs
                    continue;
                }
            }
        }

        // Calculate portfolio percentages
        $totalValue = array_sum(array_column($positions, 'currentValue'));
        
        foreach ($positions as &$position) {
            $position['percentage'] = $totalValue > 0 ? ($position['currentValue'] / $totalValue) * 100 : 0;
        }

        return $positions;
    }

    /**
     * Get position for a specific symbol
     */
    public function getPosition(string $symbol): array
    {
        $positions = $this->getOpenPositions();
        
        foreach ($positions as $position) {
            if ($position['symbol'] === strtoupper($symbol)) {
                return $position;
            }
        }

        return [
            'symbol' => strtoupper($symbol),
            'quantity' => 0,
            'free' => 0,
            'locked' => 0,
            'currentPrice' => 0,
            'currentValue' => 0,
            'percentage' => 0,
        ];
    }

    /**
     * Get position history by analyzing trade history
     */
    public function getPositionHistory(string $symbol, array $options = []): array
    {
        $trades = $this->api->account()->getTradeHistory($symbol, $options);
        
        $positions = [];
        $currentQuantity = 0;
        $totalCost = 0;
        $totalFees = 0;
        
        foreach ($trades as $trade) {
            $quantity = (float) $trade['qty'];
            $price = (float) $trade['price'];
            $fee = (float) $trade['commission'];
            $isBuyer = $trade['isBuyer'];
            
            if ($isBuyer) {
                // Buy trade
                $currentQuantity += $quantity;
                $totalCost += $quantity * $price;
                $totalFees += $fee;
            } else {
                // Sell trade
                $currentQuantity -= $quantity;
                $totalCost -= ($totalCost / ($currentQuantity + $quantity)) * $quantity; // Average cost basis
                $totalFees += $fee;
            }
            
            $avgPrice = $currentQuantity > 0 ? $totalCost / $currentQuantity : 0;
            
            $positions[] = [
                'time' => $trade['time'],
                'side' => $isBuyer ? 'BUY' : 'SELL',
                'quantity' => $quantity,
                'price' => $price,
                'fee' => $fee,
                'currentQuantity' => $currentQuantity,
                'averagePrice' => $avgPrice,
                'totalCost' => $totalCost,
                'totalFees' => $totalFees,
            ];
        }
        
        return array_reverse($positions); // Most recent first
    }

    /**
     * Calculate PnL for a position
     */
    public function calculatePnL(string $symbol, float $entryPrice = null): array
    {
        $position = $this->getPosition($symbol);
        
        if ($position['quantity'] <= 0) {
            return [
                'symbol' => $symbol,
                'unrealizedPnL' => 0,
                'unrealizedPnLPercent' => 0,
                'quantity' => 0,
                'entryPrice' => 0,
                'currentPrice' => 0,
            ];
        }
        
        // If no entry price provided, try to calculate from trade history
        if ($entryPrice === null) {
            $history = $this->getPositionHistory($symbol);
            $entryPrice = !empty($history) ? end($history)['averagePrice'] : 0;
        }
        
        $currentPrice = $position['currentPrice'];
        $quantity = $position['quantity'];
        
        $unrealizedPnL = ($currentPrice - $entryPrice) * $quantity;
        $unrealizedPnLPercent = $entryPrice > 0 ? (($currentPrice - $entryPrice) / $entryPrice) * 100 : 0;
        
        return [
            'symbol' => $symbol,
            'unrealizedPnL' => $unrealizedPnL,
            'unrealizedPnLPercent' => $unrealizedPnLPercent,
            'quantity' => $quantity,
            'entryPrice' => $entryPrice,
            'currentPrice' => $currentPrice,
            'currentValue' => $position['currentValue'],
        ];
    }

    /**
     * Get portfolio summary
     */
    public function getPortfolioSummary(): array
    {
        $positions = $this->getOpenPositions();
        $account = $this->api->account()->getAccountInfo();
        
        // Get USDT balance
        $usdtBalance = 0;
        foreach ($account['balances'] as $balance) {
            if ($balance['asset'] === 'USDT') {
                $usdtBalance = (float) $balance['free'] + (float) $balance['locked'];
                break;
            }
        }
        
        $totalValue = array_sum(array_column($positions, 'currentValue')) + $usdtBalance;
        $totalAssets = count($positions) + ($usdtBalance > 0 ? 1 : 0);
        
        return [
            'totalValue' => $totalValue,
            'totalAssets' => $totalAssets,
            'usdtBalance' => $usdtBalance,
            'cryptoValue' => $totalValue - $usdtBalance,
            'positions' => $positions,
            'timestamp' => time(),
        ];
    }

    /**
     * Get portfolio allocation breakdown
     */
    public function getPortfolioAllocation(): array
    {
        $summary = $this->getPortfolioSummary();
        $allocations = [];
        
        // Add crypto positions
        foreach ($summary['positions'] as $position) {
            $allocations[] = [
                'asset' => $position['asset'],
                'value' => $position['currentValue'],
                'percentage' => $position['percentage'],
                'type' => 'crypto',
            ];
        }
        
        // Add USDT if present
        if ($summary['usdtBalance'] > 0) {
            $percentage = $summary['totalValue'] > 0 ? ($summary['usdtBalance'] / $summary['totalValue']) * 100 : 0;
            $allocations[] = [
                'asset' => 'USDT',
                'value' => $summary['usdtBalance'],
                'percentage' => $percentage,
                'type' => 'stablecoin',
            ];
        }
        
        // Sort by value descending
        usort($allocations, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });
        
        return $allocations;
    }

    /**
     * Get position performance metrics
     */
    public function getPositionMetrics(string $symbol, int $days = 30): array
    {
        $endTime = time() * 1000;
        $startTime = $endTime - ($days * 24 * 60 * 60 * 1000);
        
        // Get trade history for the period
        $trades = $this->api->account()->getTradeHistory($symbol, [
            'startTime' => $startTime,
            'endTime' => $endTime,
        ]);
        
        if (empty($trades)) {
            return [
                'totalTrades' => 0,
                'totalVolume' => 0,
                'totalFees' => 0,
                'profitLoss' => 0,
                'winRate' => 0,
            ];
        }
        
        $totalVolume = 0;
        $totalFees = 0;
        $buyVolume = 0;
        $sellVolume = 0;
        $wins = 0;
        $totalTrades = count($trades);
        
        foreach ($trades as $trade) {
            $quantity = (float) $trade['qty'];
            $price = (float) $trade['price'];
            $fee = (float) $trade['commission'];
            $volume = $quantity * $price;
            
            $totalVolume += $volume;
            $totalFees += $fee;
            
            if ($trade['isBuyer']) {
                $buyVolume += $volume;
            } else {
                $sellVolume += $volume;
                // Simple win detection (selling higher than average buy price would require more complex tracking)
            }
        }
        
        $profitLoss = $sellVolume - $buyVolume - $totalFees; // Simplified calculation
        $winRate = $totalTrades > 0 ? ($wins / $totalTrades) * 100 : 0;
        
        return [
            'totalTrades' => $totalTrades,
            'totalVolume' => $totalVolume,
            'totalFees' => $totalFees,
            'profitLoss' => $profitLoss,
            'winRate' => $winRate,
            'buyVolume' => $buyVolume,
            'sellVolume' => $sellVolume,
        ];
    }

    /**
     * Get risk metrics for portfolio
     */
    public function getRiskMetrics(): array
    {
        $allocations = $this->getPortfolioAllocation();
        $summary = $this->getPortfolioSummary();
        
        // Calculate concentration risk (Herfindahl Index)
        $herfindahlIndex = 0;
        foreach ($allocations as $allocation) {
            $percentage = $allocation['percentage'] / 100;
            $herfindahlIndex += $percentage * $percentage;
        }
        
        // Calculate diversification metrics
        $cryptoCount = count(array_filter($allocations, function ($a) {
            return $a['type'] === 'crypto';
        }));
        
        $largestPosition = !empty($allocations) ? $allocations[0]['percentage'] : 0;
        $stablecoinPercentage = 0;
        
        foreach ($allocations as $allocation) {
            if ($allocation['type'] === 'stablecoin') {
                $stablecoinPercentage += $allocation['percentage'];
            }
        }
        
        return [
            'concentrationRisk' => $herfindahlIndex,
            'diversificationScore' => 1 - $herfindahlIndex, // Higher is better
            'cryptoAssetCount' => $cryptoCount,
            'largestPositionPercent' => $largestPosition,
            'stablecoinPercent' => $stablecoinPercentage,
            'riskLevel' => $this->calculateRiskLevel($herfindahlIndex, $largestPosition),
        ];
    }

    /**
     * Calculate overall risk level
     */
    private function calculateRiskLevel(float $concentration, float $largestPosition): string
    {
        if ($concentration > 0.7 || $largestPosition > 70) {
            return 'HIGH';
        } elseif ($concentration > 0.4 || $largestPosition > 40) {
            return 'MEDIUM';
        } else {
            return 'LOW';
        }
    }
}
