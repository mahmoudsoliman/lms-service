<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Domain\Model\Course;
use Lms\Domain\Model\Homework;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use PHPUnit\Framework\TestCase;

final class HomeworkAvailabilityTest extends TestCase
{
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
    }

    public function testHomeworkIsAvailableAtCourseStart(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $homework = new Homework(
            new ContentId('homework-1'),
            'Label a Plant Cell'
        );

        $this->assertTrue($homework->isAvailableAt($courseStart, $course));
    }

    public function testHomeworkIsAvailableAfterCourseStart(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $homework = new Homework(
            new ContentId('homework-1'),
            'Label a Plant Cell'
        );

        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);
        $this->assertTrue($homework->isAvailableAt($at, $course));
    }

    public function testHomeworkIsNotAvailableBeforeCourseStart(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $homework = new Homework(
            new ContentId('homework-1'),
            'Label a Plant Cell'
        );

        $at = new DateTimeImmutable('2025-05-12 23:59:59', $this->timezone);
        $this->assertFalse($homework->isAvailableAt($at, $course));
    }
}

