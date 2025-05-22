<?php

namespace MrAbdelaziz\BinanceApi\Tests\Unit;

use MrAbdelaziz\BinanceApi\Exceptions\BinanceApiException;
use MrAbdelaziz\BinanceApi\Exceptions\RateLimitException;
use MrAbdelaziz\BinanceApi\Tests\TestCase;

class ExceptionTest extends TestCase
{
    public function test_binance_api_exception_basic_functionality()
    {
        $exception = new BinanceApiException('Test message', 400);

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertNull($exception->getData());
        $this->assertNull($exception->getBinanceCode());
        $this->assertNull($exception->getBinanceMessage());
    }

    public function test_binance_api_exception_with_data()
    {
        $data = [
            'code' => -1013,
            'msg' => 'Invalid quantity.',
        ];

        $exception = new BinanceApiException('API Error', 400, $data);

        $this->assertEquals('API Error', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals($data, $exception->getData());
        $this->assertEquals(-1013, $exception->getBinanceCode());
        $this->assertEquals('Invalid quantity.', $exception->getBinanceMessage());
    }

    public function test_is_binance_error_detection()
    {
        $data = ['code' => -1013, 'msg' => 'Invalid quantity.'];
        $exception = new BinanceApiException('API Error', 400, $data);

        $this->assertTrue($exception->isBinanceError(-1013));
        $this->assertFalse($exception->isBinanceError(-1014));
    }

    public function test_is_authentication_error_detection()
    {
        $authData = ['code' => -2014, 'msg' => 'API-key format invalid.'];
        $exception = new BinanceApiException('Auth Error', 401, $authData);

        $this->assertTrue($exception->isAuthenticationError());

        $nonAuthData = ['code' => -1013, 'msg' => 'Invalid quantity.'];
        $exception2 = new BinanceApiException('Other Error', 400, $nonAuthData);

        $this->assertFalse($exception2->isAuthenticationError());
    }

    public function test_is_rate_limit_error_detection()
    {
        $rateLimitData = ['code' => -1003, 'msg' => 'Too many requests.'];
        $exception = new BinanceApiException('Rate Limit Error', 429, $rateLimitData);

        $this->assertTrue($exception->isRateLimitError());

        $nonRateLimitData = ['code' => -1013, 'msg' => 'Invalid quantity.'];
        $exception2 = new BinanceApiException('Other Error', 400, $nonRateLimitData);

        $this->assertFalse($exception2->isRateLimitError());
    }

    public function test_is_insufficient_balance_error_detection()
    {
        $balanceData = ['code' => -2010, 'msg' => 'Account has insufficient balance.'];
        $exception = new BinanceApiException('Balance Error', 400, $balanceData);

        $this->assertTrue($exception->isInsufficientBalanceError());
    }

    public function test_is_symbol_error_detection()
    {
        $symbolData = ['code' => -1121, 'msg' => 'Invalid symbol.'];
        $exception = new BinanceApiException('Symbol Error', 400, $symbolData);

        $this->assertTrue($exception->isSymbolError());
    }

    public function test_get_user_friendly_message()
    {
        // Test specific error codes
        $exception1 = new BinanceApiException('', 0, ['code' => -1003]);
        $this->assertEquals('Too many requests. Please wait and try again.', $exception1->getUserFriendlyMessage());

        $exception2 = new BinanceApiException('', 0, ['code' => -2010]);
        $this->assertEquals('Insufficient account balance.', $exception2->getUserFriendlyMessage());

        $exception3 = new BinanceApiException('', 0, ['code' => -1121]);
        $this->assertEquals('Invalid symbol.', $exception3->getUserFriendlyMessage());

        // Test fallback to binance message
        $exception4 = new BinanceApiException('', 0, ['code' => -9999, 'msg' => 'Custom error message']);
        $this->assertEquals('Custom error message', $exception4->getUserFriendlyMessage());

        // Test fallback to exception message
        $exception5 = new BinanceApiException('Fallback message', 0, ['code' => -9999]);
        $this->assertEquals('Fallback message', $exception5->getUserFriendlyMessage());
    }

    public function test_to_array_method()
    {
        $data = ['code' => -1013, 'msg' => 'Invalid quantity.'];
        $exception = new BinanceApiException('API Error', 400, $data);

        $array = $exception->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('API Error', $array['message']);
        $this->assertEquals(400, $array['code']);
        $this->assertEquals(-1013, $array['binance_code']);
        $this->assertEquals('Invalid quantity.', $array['binance_message']);
        $this->assertEquals($data, $array['data']);
        $this->assertArrayHasKey('file', $array);
        $this->assertArrayHasKey('line', $array);
        $this->assertArrayHasKey('trace', $array);
    }

    public function test_rate_limit_exception()
    {
        $exception = new RateLimitException('Rate limit exceeded', 120, 429);

        $this->assertEquals('Rate limit exceeded', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
        $this->assertEquals(120, $exception->getRetryAfter());

        $exception->setRetryAfter(60);
        $this->assertEquals(60, $exception->getRetryAfter());
    }

    public function test_rate_limit_exception_defaults()
    {
        $exception = new RateLimitException;

        $this->assertEquals('Rate limit exceeded', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
        $this->assertEquals(60, $exception->getRetryAfter());
    }
}
