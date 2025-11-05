<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Domain\Model\Course;
use Lms\Domain\Model\Lesson;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use PHPUnit\Framework\TestCase;

final class LessonAvailabilityTest extends TestCase
{
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
    }

    public function testLessonIsAvailableAtScheduledTime(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $scheduledAt = new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone);
        $lesson = new Lesson(
            new ContentId('lesson-1'),
            'Cell Structure',
            $scheduledAt
        );

        $this->assertTrue($lesson->isAvailableAt($scheduledAt, $course));
    }

    public function testLessonIsAvailableAfterScheduledTime(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $scheduledAt = new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone);
        $lesson = new Lesson(
            new ContentId('lesson-1'),
            'Cell Structure',
            $scheduledAt
        );

        $at = new DateTimeImmutable('2025-05-15 10:01:00', $this->timezone);
        $this->assertTrue($lesson->isAvailableAt($at, $course));
    }

    public function testLessonIsNotAvailableBeforeScheduledTime(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $scheduledAt = new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone);
        $lesson = new Lesson(
            new ContentId('lesson-1'),
            'Cell Structure',
            $scheduledAt
        );

        $at = new DateTimeImmutable('2025-05-15 09:59:59', $this->timezone);
        $this->assertFalse($lesson->isAvailableAt($at, $course));
    }
}

