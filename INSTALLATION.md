# Binance API Package Installation Guide

This guide will help you install and configure the Binance API package for Laravel.

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Composer
- MySQL 5.7+ or MariaDB 10.2+

## Installation Steps

### 1. Install the Package

Add the package to your Laravel project:

```bash
# If installing as a local package
composer require MrAbdelaziz/binance-api

# Or if developing locally, add to composer.json
"repositories": [
    {
        "type": "path",
        "url": "./packages/binance-api"
    }
],
"require": {
    "MrAbdelaziz/binance-api": "*"
}
```

### 2. Publish Configuration

Publish the package configuration file:

```bash
php artisan vendor:publish --provider="MrAbdelaziz\BinanceApi\BinanceApiServiceProvider" --tag="binance-api-config"
```

This will create `config/binance-api.php` file.

### 3. Environment Configuration

Add your Binance API credentials to your `.env` file:

```env
# Binance API Configuration
BINANCE_API_KEY=your_api_key_here
BINANCE_API_SECRET=your_api_secret_here
BINANCE_BASE_URL=https://api.binance.com
BINANCE_TESTNET=false
BINANCE_TIMEOUT=30

# Rate Limiting
BINANCE_LOGGING_ENABLED=true
BINANCE_LOG_REQUESTS=false
BINANCE_LOG_RESPONSES=false
BINANCE_LOG_ERRORS=true

# Caching
BINANCE_CACHE_ENABLED=true
```

### 4. Get Binance API Keys

1. Go to [Binance API Management](https://www.binance.com/en/my/settings/api-management)
2. Create a new API key
3. Enable "Enable Spot & Margin Trading" if you want to place orders
4. Set IP restrictions for security (recommended)
5. Copy the API Key and Secret Key to your `.env` file

**Important Security Notes:**
- Never commit your API keys to version control
- Use IP restrictions on your Binance API keys
- Enable only the permissions you need
- Consider using testnet for development

### 5. Add Routes (Optional)

If you want to use the provided API endpoints, add the routes to your `routes/api.php`:

```php
// Include Binance API routes
require __DIR__ . '/api-binance.php';
```

Or manually add specific routes you need.

### 6. Register Service Provider (Laravel < 11)

If you're using Laravel < 11, add the service provider to `config/app.php`:

```php
'providers' => [
    // Other providers...
    MrAbdelaziz\BinanceApi\BinanceApiServiceProvider::class,
],
```

For Laravel 11+, the package will be auto-discovered.

## Configuration Options

### Basic Configuration

Edit `config/binance-api.php` to customize the package:

```php
<?php

return [
    // API Credentials
    'api_key' => env('BINANCE_API_KEY'),
    'api_secret' => env('BINANCE_API_SECRET'),
    
    // Base URL (use testnet for testing)
    'base_url' => env('BINANCE_BASE_URL', 'https://api.binance.com'),
    'testnet' => env('BINANCE_TESTNET', false),
    
    // Request timeout
    'timeout' => env('BINANCE_TIMEOUT', 30),
    
    // Rate limits
    'rate_limits' => [
        'requests_per_minute' => 1200,
        'orders_per_second' => 10,
        'orders_per_day' => 200000,
    ],
    
    // Caching configuration
    'cache' => [
        'enabled' => env('BINANCE_CACHE_ENABLED', true),
        'ttl' => [
            'market_data' => 60,
            'exchange_info' => 3600,
            'account_info' => 30,
        ],
    ],
];
```

### Testnet Configuration

For development and testing, you can use Binance Testnet:

```env
BINANCE_TESTNET=true
BINANCE_API_KEY=your_testnet_api_key
BINANCE_API_SECRET=your_testnet_api_secret
```

Get testnet API keys from: https://testnet.binance.vision/

## Usage Examples

### Basic Usage

```php
use MrAbdelaziz\BinanceApi\Facades\BinanceApi;

// Get account information
$account = BinanceApi::account()->getAccountInfo();

// Get account balances
$balances = BinanceApi::account()->getAccountBalances();

// Get current price
$price = BinanceApi::market()->getPrice('BTCUSDT');

// Place a market buy order
$order = BinanceApi::orders()->marketBuy('BTCUSDT', 0.001);
```

### Using the Enhanced Service

```php
use App\Services\EnhancedBinanceService;

$binanceService = app(EnhancedBinanceService::class);

// Get real-time portfolio summary
$portfolio = $binanceService->getPortfolioSummary();

// Test API connection
$status = $binanceService->testApiConnection();

// Sync recent orders
$synced = $binanceService->autoSyncRecentOrders(24); // Last 24 hours
```

## Console Commands

The package includes several Artisan commands:

```bash
# Sync account data from Binance
php artisan binance:sync-account --all

# Sync only positions
php artisan binance:sync-account --positions

# Sync only orders
php artisan binance:sync-account --orders

# Test API connection
php artisan binance:test-connection
```

## API Endpoints

If you included the API routes, you can access:

```bash
# Test connection
GET /api/binance/connection/test

# Get account info
GET /api/binance/account

# Get balances
GET /api/binance/balances

# Get positions
GET /api/binance/positions

# Place order
POST /api/binance/orders
{
    "symbol": "BTCUSDT",
    "side": "BUY",
    "type": "MARKET",
    "quantity": 0.001
}

# Get open orders
GET /api/binance/orders/open

# Get portfolio metrics
GET /api/binance/portfolio/metrics
```

## Troubleshooting

### Common Issues

1. **API Key Errors**
   - Verify your API key and secret are correct
   - Check that IP restrictions allow your server
   - Ensure required permissions are enabled

2. **Rate Limit Errors**
   - The package automatically handles rate limiting
   - Check rate limit status: `GET /api/binance/rate-limits`
   - Reduce request frequency if needed

3. **Connection Errors**
   - Verify internet connectivity
   - Check if Binance API is accessible from your server
   - Test with: `php artisan binance:test-connection`

4. **Permission Errors**
   - Enable "Spot Trading" permission for trading operations
   - Some endpoints require specific permissions

### Debug Mode

Enable detailed logging for debugging:

```env
BINANCE_LOGGING_ENABLED=true
BINANCE_LOG_REQUESTS=true
BINANCE_LOG_RESPONSES=true
BINANCE_LOG_ERRORS=true
```

### Testing

Test your installation:

```bash
# Test API connection
curl -X GET "http://your-app.com/api/binance/connection/test"

# Get account info
curl -X GET "http://your-app.com/api/binance/account"
```

## Security Best Practices

1. **API Key Security**
   - Use IP restrictions on Binance
   - Enable only required permissions
   - Rotate keys regularly
   - Never commit keys to version control

2. **Server Security**
   - Use HTTPS for all API communications
   - Implement proper authentication for your endpoints
   - Monitor for unusual activity
   - Use rate limiting on your endpoints

3. **Data Protection**
   - Don't log sensitive data
   - Encrypt stored API keys
   - Use secure communication channels
   - Implement proper access controls

## Support

For issues and questions:

1. Check the package documentation
2. Review Binance API documentation: https://binance-docs.github.io/apidocs/spot/en/
3. Check logs for detailed error information
4. Create an issue on the package repository

## Next Steps

After installation:

1. Test your API connection
2. Sync your account data
3. Set up monitoring and alerting
4. Implement proper error handling
5. Configure rate limiting
6. Set up automated synchronization
