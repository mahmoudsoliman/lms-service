<?php

declare(strict_types=1);

namespace Lms\Domain\Exception;

use Lms\Domain\Enum\AccessDenialReason;
use RuntimeException;

final class AccessDeniedException extends RuntimeException
{
    public function __construct(
        public readonly AccessDenialReason $reason,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message ?: "Access denied: {$reason->value}", 0, $previous);
    }
}

