<?php

namespace Modules\APIPartnerFizaHUB\Support;

use RuntimeException;

class PartnerApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public string $errorCode,
        string $message,
        public int $status = 409,
        public array $details = []
    ) {
        parent::__construct($message);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function make(string $errorCode, string $message, int $status = 409, array $details = []): self
    {
        return new self($errorCode, $message, $status, $details);
    }
}
