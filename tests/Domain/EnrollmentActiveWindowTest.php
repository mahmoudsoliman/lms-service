<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Domain\Model\Enrollment;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use Lms\Domain\Model\Value\StudentId;
use PHPUnit\Framework\TestCase;

final class EnrollmentActiveWindowTest extends TestCase
{
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
    }

    public function testEnrollmentCreation(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $start = new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone);
        $end = new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone);
        $period = new DateRange($start, $end);

        $enrollment = new Enrollment($studentId, $courseId, $period);

        $this->assertEquals($studentId, $enrollment->studentId);
        $this->assertEquals($courseId, $enrollment->courseId);
        $this->assertEquals($period, $enrollment->period);
    }

    public function testEnrollmentIsActiveDuringPeriod(): void
    {
        $start = new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone);
        $end = new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone);
        $period = new DateRange($start, $end);
        $enrollment = new Enrollment(
            new StudentId('student-1'),
            new CourseId('course-1'),
            $period
        );

        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);
        $this->assertTrue($enrollment->period->contains($at));
    }

    public function testEnrollmentIsActiveAtStartBoundary(): void
    {
        $start = new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone);
        $end = new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone);
        $period = new DateRange($start, $end);
        $enrollment = new Enrollment(
            new StudentId('student-1'),
            new CourseId('course-1'),
            $period
        );

        $this->assertTrue($enrollment->period->contains($start));
    }

    public function testEnrollmentIsActiveAtEndBoundary(): void
    {
        $start = new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone);
        $end = new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone);
        $period = new DateRange($start, $end);
        $enrollment = new Enrollment(
            new StudentId('student-1'),
            new CourseId('course-1'),
            $period
        );

        $this->assertTrue($enrollment->period->contains($end));
    }

    public function testEnrollmentIsNotActiveBeforeStart(): void
    {
        $start = new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone);
        $end = new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone);
        $period = new DateRange($start, $end);
        $enrollment = new Enrollment(
            new StudentId('student-1'),
            new CourseId('course-1'),
            $period
        );

        $at = new DateTimeImmutable('2025-04-30 23:59:59', $this->timezone);
        $this->assertFalse($enrollment->period->contains($at));
    }

    public function testEnrollmentIsNotActiveAfterEnd(): void
    {
        $start = new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone);
        $end = new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone);
        $period = new DateRange($start, $end);
        $enrollment = new Enrollment(
            new StudentId('student-1'),
            new CourseId('course-1'),
            $period
        );

        $at = new DateTimeImmutable('2025-05-31 00:00:00', $this->timezone);
        $this->assertFalse($enrollment->period->contains($at));
    }
}

