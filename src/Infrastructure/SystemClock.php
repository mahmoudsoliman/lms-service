<?php

declare(strict_types=1);

namespace Lms\Infrastructure;

use DateTimeImmutable;
use Lms\Domain\Interfaces\Clock;

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

