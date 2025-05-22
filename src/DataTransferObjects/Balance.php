<?php

namespace MrAbdelaziz\BinanceApi\DataTransferObjects;

class Balance
{
    public readonly float $total;

    public function __construct(
        public readonly string $asset,
        public readonly float $free,
        public readonly float $locked
    ) {
        $this->total = $this->free + $this->locked;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            asset: $data['asset'],
            free: (float) $data['free'],
            locked: (float) $data['locked']
        );
    }

    public function toArray(): array
    {
        return [
            'asset' => $this->asset,
            'free' => $this->free,
            'locked' => $this->locked,
            'total' => $this->total,
        ];
    }

    /**
     * Check if this balance has any funds
     */
    public function hasBalance(): bool
    {
        return $this->total > 0;
    }

    /**
     * Check if there are locked funds
     */
    public function hasLockedFunds(): bool
    {
        return $this->locked > 0;
    }

    /**
     * Get available balance (free funds only)
     */
    public function getAvailable(): float
    {
        return $this->free;
    }

    /**
     * Check if sufficient balance for amount
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->free >= $amount;
    }
}
