<?php

namespace MrAbdelaziz\BinanceApi\Facades;

use Illuminate\Support\Facades\Facade;
use MrAbdelaziz\BinanceApi\Services\BinanceApiService;

/**
 * @method static \MrAbdelaziz\BinanceApi\Services\AccountService account()
 * @method static \MrAbdelaziz\BinanceApi\Services\OrderService orders()
 * @method static \MrAbdelaziz\BinanceApi\Services\MarketService market()
 * @method static \MrAbdelaziz\BinanceApi\Services\PositionService positions()
 * @method static array serverTime()
 * @method static array exchangeInfo()
 * @method static array ping()
 * 
 * @see \MrAbdelaziz\BinanceApi\Services\BinanceApiService
 */
class BinanceApi extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'binance-api';
    }
}
