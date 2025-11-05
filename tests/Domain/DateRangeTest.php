<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Domain\Model\Value\DateRange;
use PHPUnit\Framework\TestCase;

final class DateRangeTest extends TestCase
{
    private DateTimeImmutable $start;
    private DateTimeImmutable $end;
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
        $this->start = new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone);
        $this->end = new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone);
    }

    public function testContainsWithDateInRange(): void
    {
        $range = new DateRange($this->start, $this->end);
        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);

        $this->assertTrue($range->contains($at));
    }

    public function testContainsWithDateAtStartBoundary(): void
    {
        $range = new DateRange($this->start, $this->end);

        $this->assertTrue($range->contains($this->start));
    }

    public function testContainsWithDateAtEndBoundary(): void
    {
        $range = new DateRange($this->start, $this->end);

        $this->assertTrue($range->contains($this->end));
    }

    public function testContainsWithDateBeforeStart(): void
    {
        $range = new DateRange($this->start, $this->end);
        $at = new DateTimeImmutable('2025-04-30 23:59:59', $this->timezone);

        $this->assertFalse($range->contains($at));
    }

    public function testContainsWithDateAfterEnd(): void
    {
        $range = new DateRange($this->start, $this->end);
        $at = new DateTimeImmutable('2025-05-31 00:00:00', $this->timezone);

        $this->assertFalse($range->contains($at));
    }

    public function testContainsWithOpenEndedRange(): void
    {
        $range = new DateRange($this->start, null);
        $at = new DateTimeImmutable('2025-06-15 12:00:00', $this->timezone);

        $this->assertTrue($range->contains($at));
    }

    public function testHasStartedAtWithDateBeforeStart(): void
    {
        $range = new DateRange($this->start, $this->end);
        $at = new DateTimeImmutable('2025-04-30 23:59:59', $this->timezone);

        $this->assertFalse($range->hasStartedAt($at));
    }

    public function testHasStartedAtWithDateAtStart(): void
    {
        $range = new DateRange($this->start, $this->end);

        $this->assertTrue($range->hasStartedAt($this->start));
    }

    public function testHasStartedAtWithDateAfterStart(): void
    {
        $range = new DateRange($this->start, $this->end);
        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);

        $this->assertTrue($range->hasStartedAt($at));
    }

    public function testHasEndedBeforeWithDateBeforeEnd(): void
    {
        $range = new DateRange($this->start, $this->end);
        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);

        $this->assertFalse($range->hasEndedBefore($at));
    }

    public function testHasEndedBeforeWithDateAtEnd(): void
    {
        $range = new DateRange($this->start, $this->end);

        $this->assertFalse($range->hasEndedBefore($this->end));
    }

    public function testHasEndedBeforeWithDateAfterEnd(): void
    {
        $range = new DateRange($this->start, $this->end);
        $at = new DateTimeImmutable('2025-05-31 00:00:00', $this->timezone);

        $this->assertTrue($range->hasEndedBefore($at));
    }

    public function testHasEndedBeforeWithOpenEndedRange(): void
    {
        $range = new DateRange($this->start, null);
        $at = new DateTimeImmutable('2025-06-15 12:00:00', $this->timezone);

        $this->assertFalse($range->hasEndedBefore($at));
    }
}

