<?php

declare(strict_types=1);

namespace Lms\Tests\Acceptance;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Domain\Model\Course;
use Lms\Domain\Model\Enrollment;
use Lms\Domain\Model\Homework;
use Lms\Domain\Model\Lesson;
use Lms\Domain\Model\PrepMaterial;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use Lms\Domain\Model\Value\StudentId;
use Lms\Domain\Service\AccessPolicy;
use Lms\Infrastructure\Persistence\InMemoryCourseRepository;
use Lms\Infrastructure\Persistence\InMemoryEnrollmentRepository;
use PHPUnit\Framework\TestCase;

final class ExampleUseCaseTest extends TestCase
{
    private DateTimeZone $timezone;
    private InMemoryCourseRepository $courseRepository;
    private InMemoryEnrollmentRepository $enrollmentRepository;
    private AccessPolicy $accessPolicy;
    private StudentId $emmaId;
    private CourseId $biologyCourseId;
    private ContentId $lessonId;
    private ContentId $homeworkId;
    private ContentId $prepId;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
        $this->courseRepository = new InMemoryCourseRepository();
        $this->enrollmentRepository = new InMemoryEnrollmentRepository();
        $this->accessPolicy = new AccessPolicy($this->courseRepository, $this->enrollmentRepository);

        $this->emmaId = new StudentId('emma-123');
        $this->biologyCourseId = new CourseId('biology-course-1');
        $this->lessonId = new ContentId('lesson-cell-structure');
        $this->homeworkId = new ContentId('homework-plant-cell');
        $this->prepId = new ContentId('prep-reading-guide');

        $this->setupCourse();
    }

    public function testFullEmmaScenario(): void
    {
        // Setup enrollment: 2025-05-01 → 2025-05-30
        $enrollmentStart = new DateTimeImmutable('2025-05-01 00:00:00', $this->timezone);
        $enrollmentEnd = new DateTimeImmutable('2025-05-30 23:59:59', $this->timezone);
        $enrollment = new Enrollment(
            $this->emmaId,
            $this->biologyCourseId,
            new DateRange($enrollmentStart, $enrollmentEnd)
        );
        $this->enrollmentRepository->save($enrollment);

        // Step 1: 2025-05-01: access Prep → Denied COURSE_NOT_STARTED
        $at1 = new DateTimeImmutable('2025-05-01 12:00:00', $this->timezone);
        $decision1 = $this->accessPolicy->decide($this->emmaId, $this->biologyCourseId, $this->prepId, $at1);
        $this->assertFalse($decision1->allowed);
        $this->assertEquals('COURSE_NOT_STARTED', $decision1->reason->value);

        // Step 2: 2025-05-13: access Prep → Allowed
        $at2 = new DateTimeImmutable('2025-05-13 12:00:00', $this->timezone);
        $decision2 = $this->accessPolicy->decide($this->emmaId, $this->biologyCourseId, $this->prepId, $at2);
        $this->assertTrue($decision2->allowed);
        $this->assertNull($decision2->reason);

        // Step 3: 2025-05-15 10:01: access Lesson → Allowed
        $at3 = new DateTimeImmutable('2025-05-15 10:01:00', $this->timezone);
        $decision3 = $this->accessPolicy->decide($this->emmaId, $this->biologyCourseId, $this->lessonId, $at3);
        $this->assertTrue($decision3->allowed);
        $this->assertNull($decision3->reason);

        // Step 4: Modify enrollment end to 2025-05-20
        $enrollmentEndShortened = new DateTimeImmutable('2025-05-20 23:59:59', $this->timezone);
        $updatedEnrollment = new Enrollment(
            $this->emmaId,
            $this->biologyCourseId,
            new DateRange($enrollmentStart, $enrollmentEndShortened)
        );
        $this->enrollmentRepository->save($updatedEnrollment);

        // Step 5: 2025-05-21: access Homework → Denied ENROLLMENT_NOT_ACTIVE
        $at4 = new DateTimeImmutable('2025-05-21 12:00:00', $this->timezone);
        $decision4 = $this->accessPolicy->decide($this->emmaId, $this->biologyCourseId, $this->homeworkId, $at4);
        $this->assertFalse($decision4->allowed);
        $this->assertEquals('ENROLLMENT_NOT_ACTIVE', $decision4->reason->value);

        // Step 6: 2025-05-30: access Homework → Denied ENROLLMENT_NOT_ACTIVE (because enrollment was shortened)
        $at5 = new DateTimeImmutable('2025-05-30 12:00:00', $this->timezone);
        $decision5 = $this->accessPolicy->decide($this->emmaId, $this->biologyCourseId, $this->homeworkId, $at5);
        $this->assertFalse($decision5->allowed);
        $this->assertEquals('ENROLLMENT_NOT_ACTIVE', $decision5->reason->value);

        // Step 7: 2025-06-10: course still running but not enrolled → Denied ENROLLMENT_NOT_ACTIVE
        $at6 = new DateTimeImmutable('2025-06-10 12:00:00', $this->timezone);
        $decision6 = $this->accessPolicy->decide($this->emmaId, $this->biologyCourseId, $this->homeworkId, $at6);
        $this->assertFalse($decision6->allowed);
        $this->assertEquals('ENROLLMENT_NOT_ACTIVE', $decision6->reason->value);
    }

    private function setupCourse(): void
    {
        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $courseEnd = new DateTimeImmutable('2025-06-12 23:59:59', $this->timezone);
        $period = new DateRange($courseStart, $courseEnd);

        $course = new Course(
            $this->biologyCourseId,
            'A-Level Biology',
            $period
        );

        // Lesson "Cell Structure": 2025-05-15 10:00
        $lessonScheduled = new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone);
        $lesson = new Lesson($this->lessonId, 'Cell Structure', $lessonScheduled);
        $course->addLesson($lesson);

        // Homework "Label a Plant Cell" (available from course start)
        $homework = new Homework($this->homeworkId, 'Label a Plant Cell');
        $course->addHomework($homework);

        // Prep "Biology Reading Guide" (available from course start)
        $prep = new PrepMaterial($this->prepId, 'Biology Reading Guide');
        $course->addPrepMaterial($prep);

        $this->courseRepository->save($course);
    }
}

