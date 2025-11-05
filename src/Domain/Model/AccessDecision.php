<?php

declare(strict_types=1);

namespace Lms\Domain\Model;

use Lms\Domain\Enum\AccessDenialReason;

final readonly class AccessDecision
{
    private function __construct(
        public bool $allowed,
        public ?AccessDenialReason $reason
    ) {
    }

    public static function allow(): self
    {
        return new self(true, null);
    }

    public static function deny(AccessDenialReason $reason): self
    {
        return new self(false, $reason);
    }
}

