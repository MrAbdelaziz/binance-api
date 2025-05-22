<?php

namespace MrAbdelaziz\BinanceApi\Services;

use MrAbdelaziz\BinanceApi\Exceptions\BinanceApiException;

class AccountService
{
    protected BinanceApiService $api;

    public function __construct(BinanceApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Get account information including balances and trading status
     */
    public function getAccountInfo(): array
    {
        return $this->api->signedRequest('/account');
    }

    /**
     * Get account balances only (filtered to non-zero balances)
     */
    public function getAccountBalances(bool $includeZero = false): array
    {
        $account = $this->getAccountInfo();
        $balances = $account['balances'] ?? [];

        if (! $includeZero) {
            $balances = array_filter($balances, function ($balance) {
                return (float) $balance['free'] > 0 || (float) $balance['locked'] > 0;
            });
        }

        return array_values($balances);
    }

    /**
     * Get balance for a specific asset
     */
    public function getAssetBalance(string $asset): array
    {
        $balances = $this->getAccountBalances(true);

        foreach ($balances as $balance) {
            if ($balance['asset'] === strtoupper($asset)) {
                return $balance;
            }
        }

        return [
            'asset' => strtoupper($asset),
            'free' => '0.00000000',
            'locked' => '0.00000000',
        ];
    }

    /**
     * Get account trading status
     */
    public function getAccountStatus(): array
    {
        return $this->api->signedRequest('/account/status');
    }

    /**
     * Get API key permissions
     */
    public function getApiKeyPermissions(): array
    {
        return $this->api->signedRequest('/account/apiRestrictions');
    }

    /**
     * Get account commission rates
     */
    public function getCommissionRates(string $symbol): array
    {
        return $this->api->signedRequest('/account/commission', [
            'symbol' => strtoupper($symbol),
        ]);
    }

    /**
     * Get trading fees for all symbols or specific symbol
     */
    public function getTradingFees(?string $symbol = null): array
    {
        $params = [];
        if ($symbol) {
            $params['symbol'] = strtoupper($symbol);
        }

        return $this->api->signedRequest('/asset/tradeFee', $params);
    }

    /**
     * Get account trade list (trades) for a specific symbol
     */
    public function getTradeHistory(string $symbol, array $options = []): array
    {
        $params = array_merge([
            'symbol' => strtoupper($symbol),
        ], $options);

        // Optional parameters: startTime, endTime, fromId, limit
        return $this->api->signedRequest('/myTrades', $params);
    }

    /**
     * Get deposit history
     */
    public function getDepositHistory(?string $coin = null, array $options = []): array
    {
        $params = $options;
        if ($coin) {
            $params['coin'] = strtoupper($coin);
        }

        return $this->api->signedRequest('/capital/deposit/hisrec', $params);
    }

    /**
     * Get withdraw history
     */
    public function getWithdrawHistory(?string $coin = null, array $options = []): array
    {
        $params = $options;
        if ($coin) {
            $params['coin'] = strtoupper($coin);
        }

        return $this->api->signedRequest('/capital/withdraw/history', $params);
    }

    /**
     * Get deposit address for a coin
     */
    public function getDepositAddress(string $coin, ?string $network = null): array
    {
        $params = ['coin' => strtoupper($coin)];
        if ($network) {
            $params['network'] = $network;
        }

        return $this->api->signedRequest('/capital/deposit/address', $params);
    }

    /**
     * Get all coin information (available for deposit and withdraw)
     */
    public function getAllCoinInfo(): array
    {
        return $this->api->signedRequest('/capital/config/getall');
    }

    /**
     * Get account snapshot (daily account snapshot)
     */
    public function getAccountSnapshot(string $type = 'SPOT', array $options = []): array
    {
        $params = array_merge([
            'type' => strtoupper($type), // SPOT, MARGIN, FUTURES
        ], $options);

        return $this->api->signedRequest('/accountSnapshot', $params);
    }

    /**
     * Get dust log (small balance conversions)
     */
    public function getDustLog(array $options = []): array
    {
        return $this->api->signedRequest('/asset/dribblet', $options);
    }

    /**
     * Convert dust to BNB
     */
    public function convertDustToBnb(array $assets): array
    {
        if (empty($assets)) {
            throw new BinanceApiException('Assets array cannot be empty');
        }

        return $this->api->signedRequest('/asset/dust', [
            'asset' => implode(',', array_map('strtoupper', $assets)),
        ], 'POST');
    }

    /**
     * Get asset dividend record
     */
    public function getAssetDividendRecord(array $options = []): array
    {
        return $this->api->signedRequest('/asset/assetDividend', $options);
    }

    /**
     * Get user universal transfer history
     */
    public function getUniversalTransferHistory(string $type, array $options = []): array
    {
        $params = array_merge([
            'type' => $type, // MAIN_C2C, MAIN_UMFUTURE, MAIN_CMFUTURE, etc.
        ], $options);

        return $this->api->signedRequest('/asset/transfer', $params);
    }

    /**
     * Get funding wallet balance
     */
    public function getFundingWallet(?string $asset = null, bool $needBtcValuation = false): array
    {
        $params = ['needBtcValuation' => $needBtcValuation ? 'true' : 'false'];
        if ($asset) {
            $params['asset'] = strtoupper($asset);
        }

        return $this->api->signedRequest('/asset/get-funding-asset', $params, 'POST');
    }

    /**
     * Get user asset information
     */
    public function getUserAsset(?string $asset = null, bool $needBtcValuation = false): array
    {
        $params = ['needBtcValuation' => $needBtcValuation ? 'true' : 'false'];
        if ($asset) {
            $params['asset'] = strtoupper($asset);
        }

        return $this->api->signedRequest('/asset/getUserAsset', $params, 'POST');
    }

    /**
     * Get BNB burn status
     */
    public function getBnbBurnStatus(): array
    {
        return $this->api->signedRequest('/bnbBurn');
    }

    /**
     * Toggle BNB burn on spot trades and margin interest
     */
    public function setBnbBurn(?bool $spotBnbBurn = null, ?bool $interestBnbBurn = null): array
    {
        $params = [];
        if ($spotBnbBurn !== null) {
            $params['spotBNBBurn'] = $spotBnbBurn ? 'true' : 'false';
        }
        if ($interestBnbBurn !== null) {
            $params['interestBNBBurn'] = $interestBnbBurn ? 'true' : 'false';
        }

        return $this->api->signedRequest('/bnbBurn', $params, 'POST');
    }
}
