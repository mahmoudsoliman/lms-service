<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use Lms\Domain\Interfaces\Clock;

final class FixedClock implements Clock
{
    public function __construct(
        private readonly DateTimeImmutable $fixedTime
    ) {
    }

    public function now(): DateTimeImmutable
    {
        return $this->fixedTime;
    }
}

