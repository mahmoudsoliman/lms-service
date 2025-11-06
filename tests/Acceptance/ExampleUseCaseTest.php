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
use Lms\Domain\Exception\AccessDeniedException;
use Lms\Domain\Service\AccessPolicy;
use Lms\Domain\Service\ContentAccessService;
use Lms\Infrastructure\Persistence\InMemoryCourseRepository;
use Lms\Infrastructure\Persistence\InMemoryEnrollmentRepository;
use Lms\Tests\Domain\FixedClock;
use PHPUnit\Framework\TestCase;

final class ExampleUseCaseTest extends TestCase
{
    private DateTimeZone $timezone;
    private InMemoryCourseRepository $courseRepository;
    private InMemoryEnrollmentRepository $enrollmentRepository;
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
        $clock1 = new FixedClock($at1);
        $accessPolicy1 = new AccessPolicy($this->courseRepository, $this->enrollmentRepository, $clock1);
        $contentAccessService1 = new ContentAccessService($accessPolicy1, $this->courseRepository, $clock1);
        try {
            $contentAccessService1->getPrepMaterial($this->emmaId, $this->biologyCourseId, $this->prepId);
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException $e) {
            $this->assertEquals('COURSE_NOT_STARTED', $e->reason->value);
        }

        // Step 2: 2025-05-13: access Prep → Allowed
        $at2 = new DateTimeImmutable('2025-05-13 12:00:00', $this->timezone);
        $clock2 = new FixedClock($at2);
        $accessPolicy2 = new AccessPolicy($this->courseRepository, $this->enrollmentRepository, $clock2);
        $contentAccessService2 = new ContentAccessService($accessPolicy2, $this->courseRepository, $clock2);
        $prep = $contentAccessService2->getPrepMaterial($this->emmaId, $this->biologyCourseId, $this->prepId);
        $this->assertEquals($this->prepId, $prep->id);
        $this->assertEquals('Biology Reading Guide', $prep->title);

        // Step 3: 2025-05-15 10:01: access Lesson → Allowed
        $at3 = new DateTimeImmutable('2025-05-15 10:01:00', $this->timezone);
        $clock3 = new FixedClock($at3);
        $accessPolicy3 = new AccessPolicy($this->courseRepository, $this->enrollmentRepository, $clock3);
        $contentAccessService3 = new ContentAccessService($accessPolicy3, $this->courseRepository, $clock3);
        $lesson = $contentAccessService3->getLesson($this->emmaId, $this->biologyCourseId, $this->lessonId);
        $this->assertEquals($this->lessonId, $lesson->id);
        $this->assertEquals('Cell Structure', $lesson->title);

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
        $clock4 = new FixedClock($at4);
        $accessPolicy4 = new AccessPolicy($this->courseRepository, $this->enrollmentRepository, $clock4);
        $contentAccessService4 = new ContentAccessService($accessPolicy4, $this->courseRepository, $clock4);
        try {
            $contentAccessService4->getHomework($this->emmaId, $this->biologyCourseId, $this->homeworkId);
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException $e) {
            $this->assertEquals('ENROLLMENT_NOT_ACTIVE', $e->reason->value);
        }

        // Step 6: 2025-05-30: access Homework → Denied ENROLLMENT_NOT_ACTIVE (because enrollment was shortened)
        $at5 = new DateTimeImmutable('2025-05-30 12:00:00', $this->timezone);
        $clock5 = new FixedClock($at5);
        $accessPolicy5 = new AccessPolicy($this->courseRepository, $this->enrollmentRepository, $clock5);
        $contentAccessService5 = new ContentAccessService($accessPolicy5, $this->courseRepository, $clock5);
        try {
            $contentAccessService5->getHomework($this->emmaId, $this->biologyCourseId, $this->homeworkId);
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException $e) {
            $this->assertEquals('ENROLLMENT_NOT_ACTIVE', $e->reason->value);
        }

        // Step 7: 2025-06-10: course still running but not enrolled → Denied ENROLLMENT_NOT_ACTIVE
        $at6 = new DateTimeImmutable('2025-06-10 12:00:00', $this->timezone);
        $clock6 = new FixedClock($at6);
        $accessPolicy6 = new AccessPolicy($this->courseRepository, $this->enrollmentRepository, $clock6);
        $contentAccessService6 = new ContentAccessService($accessPolicy6, $this->courseRepository, $clock6);
        try {
            $contentAccessService6->getHomework($this->emmaId, $this->biologyCourseId, $this->homeworkId);
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException $e) {
            $this->assertEquals('ENROLLMENT_NOT_ACTIVE', $e->reason->value);
        }
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

