# Binance API Package for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/MrAbdelaziz/binance-api.svg?style=flat-square)](https://packagist.org/packages/MrAbdelaziz/binance-api)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/MrAbdelaziz/binance-api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/MrAbdelaziz/binance-api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/MrAbdelaziz/binance-api.svg?style=flat-square)](https://packagist.org/packages/MrAbdelaziz/binance-api)

A comprehensive Laravel package for Binance Spot API integration with full account management capabilities.

## Features

- **Account Management**: Get account information, balances, and trading status
- **Order Management**: Place, cancel, and query orders with full order lifecycle support
- **Position Tracking**: Real-time position monitoring and historical data
- **Market Data**: Real-time prices, 24h tickers, and market information
- **Security**: HMAC-SHA256 signature authentication for all account endpoints
- **Rate Limiting**: Built-in rate limiting and retry mechanisms
- **Error Handling**: Comprehensive error handling with detailed logging
- **Laravel Integration**: Service provider, facades, and configuration
- **Testable**: Full test suite with mocking capabilities

## Installation

You can install the package via composer:

```bash
composer require MrAbdelaziz/binance-api
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="binance-api-config"
```

Add your Binance API credentials to your `.env` file:

```env
BINANCE_API_KEY=your_api_key_here
BINANCE_API_SECRET=your_api_secret_here
BINANCE_BASE_URL=https://api.binance.com
BINANCE_TESTNET=false
```

## Quick Start

### Account Information

```php
use MrAbdelaziz\BinanceApi\Facades\BinanceApi;

// Get account information
$account = BinanceApi::account()->getAccountInfo();

// Get account balances
$balances = BinanceApi::account()->getAccountBalances();

// Get account trading status
$status = BinanceApi::account()->getAccountStatus();
```

### Order Management

```php
// Place a market buy order
$order = BinanceApi::orders()->marketBuy('BTCUSDT', 0.001);

// Place a limit sell order
$order = BinanceApi::orders()->limitSell('BTCUSDT', 0.001, 50000);

// Cancel an order
$cancelled = BinanceApi::orders()->cancelOrder('BTCUSDT', $orderId);

// Get order status
$orderStatus = BinanceApi::orders()->getOrder('BTCUSDT', $orderId);

// Get all orders for a symbol
$orders = BinanceApi::orders()->getAllOrders('BTCUSDT');
```

### Market Data

```php
// Get current price
$price = BinanceApi::market()->getPrice('BTCUSDT');

// Get 24hr ticker
$ticker = BinanceApi::market()->get24hrTicker('BTCUSDT');

// Get exchange info
$exchangeInfo = BinanceApi::market()->getExchangeInfo();
```

### Position Tracking

```php
// Get open positions
$positions = BinanceApi::positions()->getOpenPositions();

// Get position for specific symbol
$position = BinanceApi::positions()->getPosition('BTCUSDT');

// Get position history
$history = BinanceApi::positions()->getPositionHistory('BTCUSDT');
```

[//]: # (## Documentation)

[//]: # ()
[//]: # (For detailed documentation, visit our [documentation site]&#40;https://docs.MrAbdelaziz.com/binance-api&#41; or check the following files:)

[//]: # ()
[//]: # (- [Installation Guide]&#40;INSTALLATION.md&#41;)

[//]: # (- [Contributing]&#40;CONTRIBUTING.md&#41;)


## Advanced Usage

### Error Handling


```php
use MrAbdelaziz\BinanceApi\Exceptions\BinanceApiException;

try {
    $order = BinanceApi::orders()->marketBuy('BTCUSDT', 0.001);
} catch (BinanceApiException $e) {
    // Handle Binance API errors
    Log::error('Binance API Error: ' . $e->getMessage());
    Log::error('Error Code: ' . $e->getCode());
    Log::error('Error Data: ' . json_encode($e->getData()));
}
```

### Rate Limiting

The package automatically handles rate limiting. You can configure the rate limits in the config file:

```php
'rate_limits' => [
    'requests_per_minute' => 1200,
    'orders_per_second' => 10,
    'orders_per_day' => 200000,
],
```

### Custom Configuration

```php
// Using custom configuration
$customApi = new BinanceApi([
    'api_key' => 'custom_key',
    'api_secret' => 'custom_secret',
    'base_url' => 'https://testnet.binance.vision',
]);

$account = $customApi->account()->getAccountInfo();
```

## Testing

```bash
composer test
```
## Documentation

For detailed documentation visit the following files:

- [Installation Guide](INSTALLATION.md)
## Security

- All account endpoints use HMAC-SHA256 signature authentication
- API keys are never logged or exposed in error messages
- Timestamps are automatically synchronized with Binance servers
- All requests use HTTPS

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
