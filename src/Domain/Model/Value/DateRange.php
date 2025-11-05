<?php

declare(strict_types=1);

namespace Lms\Domain\Model\Value;

use DateTimeImmutable;

final readonly class DateRange
{
    public function __construct(
        public DateTimeImmutable $start,
        public ?DateTimeImmutable $end = null
    ) {
    }

    public function contains(DateTimeImmutable $at): bool
    {
        if (!$this->hasStartedAt($at)) {
            return false;
        }

        if ($this->end === null) {
            return true;
        }

        return $at <= $this->end;
    }

    public function hasStartedAt(DateTimeImmutable $at): bool
    {
        return $at >= $this->start;
    }

    public function hasEndedBefore(DateTimeImmutable $at): bool
    {
        if ($this->end === null) {
            return false;
        }

        return $at > $this->end;
    }
}

