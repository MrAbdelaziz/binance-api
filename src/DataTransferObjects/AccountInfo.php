<?php

namespace MrAbdelaziz\BinanceApi\DataTransferObjects;

class AccountInfo
{
    public function __construct(
        public readonly int $makerCommission,
        public readonly int $takerCommission,
        public readonly int $buyerCommission,
        public readonly int $sellerCommission,
        public readonly bool $canTrade,
        public readonly bool $canWithdraw,
        public readonly bool $canDeposit,
        public readonly int $updateTime,
        public readonly string $accountType,
        public readonly array $balances,
        public readonly array $permissions
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            makerCommission: $data['makerCommission'],
            takerCommission: $data['takerCommission'],
            buyerCommission: $data['buyerCommission'],
            sellerCommission: $data['sellerCommission'],
            canTrade: $data['canTrade'],
            canWithdraw: $data['canWithdraw'],
            canDeposit: $data['canDeposit'],
            updateTime: $data['updateTime'],
            accountType: $data['accountType'],
            balances: array_map(fn($balance) => Balance::fromArray($balance), $data['balances']),
            permissions: $data['permissions']
        );
    }

    public function toArray(): array
    {
        return [
            'makerCommission' => $this->makerCommission,
            'takerCommission' => $this->takerCommission,
            'buyerCommission' => $this->buyerCommission,
            'sellerCommission' => $this->sellerCommission,
            'canTrade' => $this->canTrade,
            'canWithdraw' => $this->canWithdraw,
            'canDeposit' => $this->canDeposit,
            'updateTime' => $this->updateTime,
            'accountType' => $this->accountType,
            'balances' => array_map(fn($balance) => $balance->toArray(), $this->balances),
            'permissions' => $this->permissions,
        ];
    }

    /**
     * Get balance for specific asset
     */
    public function getBalance(string $asset): ?Balance
    {
        foreach ($this->balances as $balance) {
            if ($balance->asset === strtoupper($asset)) {
                return $balance;
            }
        }

        return null;
    }

    /**
     * Get non-zero balances only
     */
    public function getNonZeroBalances(): array
    {
        return array_filter($this->balances, fn($balance) => $balance->total > 0);
    }

    /**
     * Check if account can perform trading operations
     */
    public function canPerformTrading(): bool
    {
        return $this->canTrade && in_array('SPOT', $this->permissions);
    }
}
