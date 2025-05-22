<?php

namespace MrAbdelaziz\BinanceApi\Tests\Unit;

use Mockery;
use MrAbdelaziz\BinanceApi\Services\AccountService;
use MrAbdelaziz\BinanceApi\Services\BinanceApiService;
use MrAbdelaziz\BinanceApi\Tests\TestCase;

class AccountServiceTest extends TestCase
{
    protected AccountService $accountService;

    protected BinanceApiService $mockApiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApiService = Mockery::mock(BinanceApiService::class);
        $this->accountService = new AccountService($this->mockApiService);
    }

    public function test_can_initialize_account_service()
    {
        $this->assertInstanceOf(AccountService::class, $this->accountService);
    }

    public function test_get_account_info_calls_api_correctly()
    {
        $expectedResponse = [
            'makerCommission' => 10,
            'takerCommission' => 10,
            'buyerCommission' => 0,
            'sellerCommission' => 0,
            'canTrade' => true,
            'canWithdraw' => true,
            'canDeposit' => true,
            'updateTime' => 1234567890,
            'accountType' => 'SPOT',
            'balances' => [],
            'permissions' => ['SPOT'],
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/account')
            ->once()
            ->andReturn($expectedResponse);

        $result = $this->accountService->getAccountInfo();

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_account_balances_filters_zero_balances()
    {
        $accountData = [
            'balances' => [
                ['asset' => 'BTC', 'free' => '1.0', 'locked' => '0.0'],
                ['asset' => 'ETH', 'free' => '0.0', 'locked' => '0.0'],
                ['asset' => 'USDT', 'free' => '100.0', 'locked' => '50.0'],
            ],
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/account')
            ->once()
            ->andReturn($accountData);

        $balances = $this->accountService->getAccountBalances();

        $this->assertCount(2, $balances);
        $this->assertEquals('BTC', $balances[0]['asset']);
        $this->assertEquals('USDT', $balances[1]['asset']);
    }

    public function test_get_asset_balance_returns_specific_asset()
    {
        $accountData = [
            'balances' => [
                ['asset' => 'BTC', 'free' => '1.0', 'locked' => '0.0'],
                ['asset' => 'USDT', 'free' => '100.0', 'locked' => '50.0'],
            ],
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/account')
            ->once()
            ->andReturn($accountData);

        $btcBalance = $this->accountService->getAssetBalance('BTC');

        $this->assertEquals('BTC', $btcBalance['asset']);
        $this->assertEquals('1.0', $btcBalance['free']);
        $this->assertEquals('0.0', $btcBalance['locked']);
    }

    public function test_get_asset_balance_returns_zero_for_non_existent_asset()
    {
        $accountData = [
            'balances' => [
                ['asset' => 'BTC', 'free' => '1.0', 'locked' => '0.0'],
            ],
        ];

        $this->mockApiService
            ->shouldReceive('signedRequest')
            ->with('/account')
            ->once()
            ->andReturn($accountData);

        $ethBalance = $this->accountService->getAssetBalance('ETH');

        $this->assertEquals('ETH', $ethBalance['asset']);
        $this->assertEquals('0.00000000', $ethBalance['free']);
        $this->assertEquals('0.00000000', $ethBalance['locked']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
