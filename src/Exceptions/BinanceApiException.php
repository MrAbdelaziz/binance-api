<?php

namespace MrAbdelaziz\BinanceApi\Exceptions;

use Exception;
use Throwable;

class BinanceApiException extends Exception
{
    protected ?array $data;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?array $data = null,
        ?Throwable $previous = null
    ) {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get additional error data from Binance API
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Get Binance error code if available
     */
    public function getBinanceCode(): ?int
    {
        return $this->data['code'] ?? null;
    }

    /**
     * Get Binance error message if available
     */
    public function getBinanceMessage(): ?string
    {
        return $this->data['msg'] ?? null;
    }

    /**
     * Check if this is a specific Binance error
     */
    public function isBinanceError(int $binanceCode): bool
    {
        return $this->getBinanceCode() === $binanceCode;
    }

    /**
     * Check if this is an authentication error
     */
    public function isAuthenticationError(): bool
    {
        $authCodes = [-2014, -2015, -1022];

        return in_array($this->getBinanceCode(), $authCodes);
    }

    /**
     * Check if this is a rate limit error
     */
    public function isRateLimitError(): bool
    {
        $rateLimitCodes = [-1003, -1015];

        return in_array($this->getBinanceCode(), $rateLimitCodes);
    }

    /**
     * Check if this is an insufficient balance error
     */
    public function isInsufficientBalanceError(): bool
    {
        $balanceCodes = [-2010, -1013];

        return in_array($this->getBinanceCode(), $balanceCodes);
    }

    /**
     * Check if this is a symbol/market error
     */
    public function isSymbolError(): bool
    {
        $symbolCodes = [-1121, -1100];

        return in_array($this->getBinanceCode(), $symbolCodes);
    }

    /**
     * Get user-friendly error message
     */
    public function getUserFriendlyMessage(): string
    {
        $binanceCode = $this->getBinanceCode();

        return match ($binanceCode) {
            -1003 => 'Too many requests. Please wait and try again.',
            -1013 => 'Invalid quantity or amount.',
            -1015 => 'Too many requests per minute.',
            -1021 => 'Timestamp for this request is outside the valid time window.',
            -1022 => 'Invalid signature provided.',
            -2010 => 'Insufficient account balance.',
            -2014 => 'API key format invalid.',
            -2015 => 'Invalid API key, IP, or permissions for action.',
            -1100 => 'Illegal characters found in parameter.',
            -1121 => 'Invalid symbol.',
            default => $this->getBinanceMessage() ?? $this->getMessage(),
        };
    }

    /**
     * Convert to array for logging
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'binance_code' => $this->getBinanceCode(),
            'binance_message' => $this->getBinanceMessage(),
            'data' => $this->getData(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
        ];
    }
}
