<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Domain\Interfaces\Clock;
use Lms\Domain\Interfaces\CourseRepository;
use Lms\Domain\Enum\AccessDenialReason;
use Lms\Domain\Exception\AccessDeniedException;
use Lms\Domain\Model\AccessDecision;
use Lms\Domain\Model\Course;
use Lms\Domain\Model\Homework;
use Lms\Domain\Model\Lesson;
use Lms\Domain\Model\PrepMaterial;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use Lms\Domain\Model\Value\StudentId;
use Lms\Domain\Service\AccessPolicyInterface;
use Lms\Domain\Service\ContentAccessService;
use PHPUnit\Framework\TestCase;

final class ContentAccessServiceTest extends TestCase
{
    private DateTimeZone $timezone;
    private Clock $clock;
    private AccessPolicyInterface $accessPolicy;
    private CourseRepository $courseRepository;
    private ContentAccessService $contentAccessService;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
        $this->clock = new FixedClock(new DateTimeImmutable('2025-05-15 12:00:00', $this->timezone));
        $this->accessPolicy = $this->createMock(AccessPolicyInterface::class);
        $this->courseRepository = $this->createMock(CourseRepository::class);
        $this->contentAccessService = new ContentAccessService($this->accessPolicy, $this->courseRepository, $this->clock);
    }

    public function testGetContentReturnsContentWhenAccessAllowed(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('lesson-1');

        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $lesson = new Lesson($contentId, 'Cell Structure', new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone));
        $course->addLesson($lesson);

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->with($studentId, $courseId, $contentId)
            ->willReturn(AccessDecision::allow());

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->with($courseId)
            ->willReturn($course);

        $content = $this->contentAccessService->getContent($studentId, $courseId, $contentId);

        $this->assertInstanceOf(Lesson::class, $content);
        $this->assertEquals($contentId, $content->id);
    }

    public function testGetContentThrowsExceptionWhenAccessDenied(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('content-1');

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->with($studentId, $courseId, $contentId)
            ->willReturn(AccessDecision::deny(AccessDenialReason::ENROLLMENT_NOT_ACTIVE));

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied: ENROLLMENT_NOT_ACTIVE');

        $this->contentAccessService->getContent($studentId, $courseId, $contentId);
    }

    public function testGetContentThrowsExceptionWithEnrollmentNotActiveReason(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('content-1');

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::deny(AccessDenialReason::ENROLLMENT_NOT_ACTIVE));

        try {
            $this->contentAccessService->getContent($studentId, $courseId, $contentId);
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException $e) {
            $this->assertEquals(AccessDenialReason::ENROLLMENT_NOT_ACTIVE, $e->reason);
        }
    }

    public function testGetContentThrowsExceptionWithCourseNotStartedReason(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('content-1');

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::deny(AccessDenialReason::COURSE_NOT_STARTED));

        try {
            $this->contentAccessService->getContent($studentId, $courseId, $contentId);
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException $e) {
            $this->assertEquals(AccessDenialReason::COURSE_NOT_STARTED, $e->reason);
        }
    }

    public function testGetContentThrowsExceptionWithContentNotAvailableReason(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $contentId = new ContentId('content-1');

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::deny(AccessDenialReason::CONTENT_NOT_AVAILABLE));

        try {
            $this->contentAccessService->getContent($studentId, $courseId, $contentId);
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException $e) {
            $this->assertEquals(AccessDenialReason::CONTENT_NOT_AVAILABLE, $e->reason);
        }
    }

    public function testGetLessonReturnsLessonWhenAccessAllowed(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $lessonId = new ContentId('lesson-1');

        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $lesson = new Lesson($lessonId, 'Cell Structure', new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone));
        $course->addLesson($lesson);

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::allow());

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->willReturn($course);

        $result = $this->contentAccessService->getLesson($studentId, $courseId, $lessonId);

        $this->assertInstanceOf(Lesson::class, $result);
        $this->assertEquals($lessonId, $result->id);
    }

    public function testGetLessonThrowsExceptionWhenNotLesson(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $homeworkId = new ContentId('homework-1');

        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $homework = new Homework($homeworkId, 'Test Homework');
        $course->addHomework($homework);

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::allow());

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->willReturn($course);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Content is not a lesson');

        $this->contentAccessService->getLesson($studentId, $courseId, $homeworkId);
    }

    public function testGetHomeworkReturnsHomeworkWhenAccessAllowed(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $homeworkId = new ContentId('homework-1');

        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $homework = new Homework($homeworkId, 'Test Homework');
        $course->addHomework($homework);

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::allow());

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->willReturn($course);

        $result = $this->contentAccessService->getHomework($studentId, $courseId, $homeworkId);

        $this->assertInstanceOf(Homework::class, $result);
        $this->assertEquals($homeworkId, $result->id);
    }

    public function testGetHomeworkThrowsExceptionWhenNotHomework(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $lessonId = new ContentId('lesson-1');

        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $lesson = new Lesson($lessonId, 'Cell Structure', new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone));
        $course->addLesson($lesson);

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::allow());

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->willReturn($course);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Content is not homework');

        $this->contentAccessService->getHomework($studentId, $courseId, $lessonId);
    }

    public function testGetPrepMaterialReturnsPrepMaterialWhenAccessAllowed(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $prepId = new ContentId('prep-1');

        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $prep = new PrepMaterial($prepId, 'Biology Reading Guide');
        $course->addPrepMaterial($prep);

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::allow());

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->willReturn($course);

        $result = $this->contentAccessService->getPrepMaterial($studentId, $courseId, $prepId);

        $this->assertInstanceOf(PrepMaterial::class, $result);
        $this->assertEquals($prepId, $result->id);
    }

    public function testGetPrepMaterialThrowsExceptionWhenNotPrepMaterial(): void
    {
        $studentId = new StudentId('student-1');
        $courseId = new CourseId('course-1');
        $lessonId = new ContentId('lesson-1');

        $courseStart = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($courseStart, null);
        $course = new Course($courseId, 'Test Course', $period);
        $lesson = new Lesson($lessonId, 'Cell Structure', new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone));
        $course->addLesson($lesson);

        $this->accessPolicy
            ->expects($this->once())
            ->method('decide')
            ->willReturn(AccessDecision::allow());

        $this->courseRepository
            ->expects($this->once())
            ->method('get')
            ->willReturn($course);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Content is not prep material');

        $this->contentAccessService->getPrepMaterial($studentId, $courseId, $lessonId);
    }
}

