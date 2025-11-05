<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Domain\Model\Course;
use Lms\Domain\Model\PrepMaterial;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use PHPUnit\Framework\TestCase;

final class PrepMaterialAvailabilityTest extends TestCase
{
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
    }

    public function testPrepMaterialIsAvailableAtCourseStart(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $prep = new PrepMaterial(
            new ContentId('prep-1'),
            'Biology Reading Guide'
        );

        $this->assertTrue($prep->isAvailableAt($courseStart, $course));
    }

    public function testPrepMaterialIsAvailableAfterCourseStart(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $prep = new PrepMaterial(
            new ContentId('prep-1'),
            'Biology Reading Guide'
        );

        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);
        $this->assertTrue($prep->isAvailableAt($at, $course));
    }

    public function testPrepMaterialIsNotAvailableBeforeCourseStart(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );

        $prep = new PrepMaterial(
            new ContentId('prep-1'),
            'Biology Reading Guide'
        );

        $at = new DateTimeImmutable('2025-05-12 23:59:59', $this->timezone);
        $this->assertFalse($prep->isAvailableAt($at, $course));
    }
}

