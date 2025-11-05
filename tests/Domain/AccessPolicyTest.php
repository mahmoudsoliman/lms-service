<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Application\Port\CourseRepository;
use Lms\Application\Port\EnrollmentRepository;
use Lms\Domain\Enum\AccessDenialReason;
use Lms\Domain\Model\AccessDecision;
use Lms\Domain\Model\Course;
use Lms\Domain\Model\Enrollment;
use Lms\Domain\Model\Homework;
use Lms\Domain\Model\Lesson;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use Lms\Domain\Model\Value\StudentId;
use Lms\Domain\Service\AccessPolicy;
use PHPUnit\Framework\TestCase;

final class AccessPolicyTest extends TestCase
{
    private DateTimeZone $timezone;
    private CourseRepository $courseRepository;
    private EnrollmentRepository $enrollmentRepository;
    private AccessPolicy $accessPolicy;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
        $this->courseRepository = $this->createMock(CourseRepository::class);
        $this->enrollmentRepository = $this->createMock(EnrollmentRepository::class);
        $this->accessPolicy = new AccessPolicy($this->courseRepository, $this->enrollmentRepository);
    }

    public function testDeniesWhenNoActiveEnrollment(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('content-1');
        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('findActiveFor')
            ->with($studentId, $courseId, $at)
            ->willReturn(null);

        $decision = $this->accessPolicy->decide($studentId, $courseId, $contentId, $at);

        $this->assertFalse($decision->allowed);
        $this->assertEquals(AccessDenialReason::ENROLLMENT_NOT_ACTIVE, $decision->reason);
    }

    public function testDeniesWhenCourseNotStarted(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('content-1');
        $courseStart = new DateTimeImmutable('2025-05-20 00:00:00', $this->timezone);
        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);

        $enrollment = new Enrollment(
            $studentId,
            $courseId,
            new DateRange(
                new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone),
                new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone)
            )
        );

        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $homework = new Homework($contentId, 'Test Homework');
        $course->addHomework($homework);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('findActiveFor')
            ->willReturn($enrollment);

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->with($courseId)
            ->willReturn($course);

        $decision = $this->accessPolicy->decide($studentId, $courseId, $contentId, $at);

        $this->assertFalse($decision->allowed);
        $this->assertEquals(AccessDenialReason::COURSE_NOT_STARTED, $decision->reason);
    }

    public function testDeniesWhenContentNotAvailable(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('lesson-1');
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $lessonScheduled = new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone);
        $at = new DateTimeImmutable('2025-05-15 09:59:59', $this->timezone);

        $enrollment = new Enrollment(
            $studentId,
            $courseId,
            new DateRange(
                new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone),
                new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone)
            )
        );

        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $lesson = new Lesson($contentId, 'Cell Structure', $lessonScheduled);
        $course->addLesson($lesson);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('findActiveFor')
            ->willReturn($enrollment);

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->with($courseId)
            ->willReturn($course);

        $decision = $this->accessPolicy->decide($studentId, $courseId, $contentId, $at);

        $this->assertFalse($decision->allowed);
        $this->assertEquals(AccessDenialReason::CONTENT_NOT_AVAILABLE, $decision->reason);
    }

    public function testAllowsWhenAllConditionsMet(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('homework-1');
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);

        $enrollment = new Enrollment(
            $studentId,
            $courseId,
            new DateRange(
                new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone),
                new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone)
            )
        );

        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $homework = new Homework($contentId, 'Test Homework');
        $course->addHomework($homework);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('findActiveFor')
            ->willReturn($enrollment);

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->with($courseId)
            ->willReturn($course);

        $decision = $this->accessPolicy->decide($studentId, $courseId, $contentId, $at);

        $this->assertTrue($decision->allowed);
        $this->assertNull($decision->reason);
    }

    public function testDeniesWhenCourseNotFound(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('content-1');
        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);

        $enrollment = new Enrollment(
            $studentId,
            $courseId,
            new DateRange(
                new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone),
                new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone)
            )
        );

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('findActiveFor')
            ->willReturn($enrollment);

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->with($courseId)
            ->willReturn(null);

        $decision = $this->accessPolicy->decide($studentId, $courseId, $contentId, $at);

        $this->assertFalse($decision->allowed);
        $this->assertEquals(AccessDenialReason::CONTENT_NOT_AVAILABLE, $decision->reason);
    }

    public function testDeniesWhenContentNotFound(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('non-existent');
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $at = new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone);

        $enrollment = new Enrollment(
            $studentId,
            $courseId,
            new DateRange(
                new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone),
                new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone)
            )
        );

        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('findActiveFor')
            ->willReturn($enrollment);

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->with($courseId)
            ->willReturn($course);

        $decision = $this->accessPolicy->decide($studentId, $courseId, $contentId, $at);

        $this->assertFalse($decision->allowed);
        $this->assertEquals(AccessDenialReason::CONTENT_NOT_AVAILABLE, $decision->reason);
    }
}

