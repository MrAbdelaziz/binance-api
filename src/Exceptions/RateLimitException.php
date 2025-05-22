<?php

namespace MrAbdelaziz\BinanceApi\Exceptions;

class RateLimitException extends BinanceApiException
{
    protected int $retryAfter;

    public function __construct(
        string $message = 'Rate limit exceeded',
        int $retryAfter = 60,
        int $code = 429
    ) {
        $this->retryAfter = $retryAfter;
        parent::__construct($message, $code);
    }

    /**
     * Get the number of seconds to wait before retrying
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Set the retry after period
     */
    public function setRetryAfter(int $seconds): void
    {
        $this->retryAfter = $seconds;
    }
}
