<?php

namespace MrAbdelaziz\BinanceApi\DataTransferObjects;

class Order
{
    public function __construct(
        public readonly string $symbol,
        public readonly int $orderId,
        public readonly int $orderListId,
        public readonly string $clientOrderId,
        public readonly float $price,
        public readonly float $origQty,
        public readonly float $executedQty,
        public readonly float $cummulativeQuoteQty,
        public readonly string $status,
        public readonly string $timeInForce,
        public readonly string $type,
        public readonly string $side,
        public readonly ?float $stopPrice,
        public readonly ?float $icebergQty,
        public readonly int $time,
        public readonly int $updateTime,
        public readonly bool $isWorking,
        public readonly float $origQuoteOrderQty,
        public readonly ?string $workingTime,
        public readonly ?string $selfTradePreventionMode
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            symbol: $data['symbol'],
            orderId: $data['orderId'],
            orderListId: $data['orderListId'] ?? -1,
            clientOrderId: $data['clientOrderId'],
            price: (float) $data['price'],
            origQty: (float) $data['origQty'],
            executedQty: (float) $data['executedQty'],
            cummulativeQuoteQty: (float) $data['cummulativeQuoteQty'],
            status: $data['status'],
            timeInForce: $data['timeInForce'],
            type: $data['type'],
            side: $data['side'],
            stopPrice: isset($data['stopPrice']) ? (float) $data['stopPrice'] : null,
            icebergQty: isset($data['icebergQty']) ? (float) $data['icebergQty'] : null,
            time: $data['time'],
            updateTime: $data['updateTime'],
            isWorking: $data['isWorking'],
            origQuoteOrderQty: (float) $data['origQuoteOrderQty'],
            workingTime: $data['workingTime'] ?? null,
            selfTradePreventionMode: $data['selfTradePreventionMode'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'symbol' => $this->symbol,
            'orderId' => $this->orderId,
            'orderListId' => $this->orderListId,
            'clientOrderId' => $this->clientOrderId,
            'price' => $this->price,
            'origQty' => $this->origQty,
            'executedQty' => $this->executedQty,
            'cummulativeQuoteQty' => $this->cummulativeQuoteQty,
            'status' => $this->status,
            'timeInForce' => $this->timeInForce,
            'type' => $this->type,
            'side' => $this->side,
            'stopPrice' => $this->stopPrice,
            'icebergQty' => $this->icebergQty,
            'time' => $this->time,
            'updateTime' => $this->updateTime,
            'isWorking' => $this->isWorking,
            'origQuoteOrderQty' => $this->origQuoteOrderQty,
            'workingTime' => $this->workingTime,
            'selfTradePreventionMode' => $this->selfTradePreventionMode,
        ];
    }

    /**
     * Check if order is filled
     */
    public function isFilled(): bool
    {
        return $this->status === 'FILLED';
    }

    /**
     * Check if order is partially filled
     */
    public function isPartiallyFilled(): bool
    {
        return $this->status === 'PARTIALLY_FILLED';
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['NEW', 'PARTIALLY_FILLED']);
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return in_array($this->status, ['CANCELED', 'EXPIRED', 'REJECTED']);
    }

    /**
     * Get remaining quantity
     */
    public function getRemainingQty(): float
    {
        return $this->origQty - $this->executedQty;
    }

    /**
     * Get fill percentage
     */
    public function getFillPercentage(): float
    {
        if ($this->origQty == 0) {
            return 0;
        }

        return ($this->executedQty / $this->origQty) * 100;
    }

    /**
     * Get average execution price
     */
    public function getAvgPrice(): float
    {
        if ($this->executedQty == 0) {
            return 0;
        }

        return $this->cummulativeQuoteQty / $this->executedQty;
    }

    /**
     * Check if this is a buy order
     */
    public function isBuy(): bool
    {
        return $this->side === 'BUY';
    }

    /**
     * Check if this is a sell order
     */
    public function isSell(): bool
    {
        return $this->side === 'SELL';
    }

    /**
     * Check if this is a market order
     */
    public function isMarketOrder(): bool
    {
        return $this->type === 'MARKET';
    }

    /**
     * Check if this is a limit order
     */
    public function isLimitOrder(): bool
    {
        return $this->type === 'LIMIT';
    }
}
