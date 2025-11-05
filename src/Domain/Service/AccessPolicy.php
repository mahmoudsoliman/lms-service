<?php

declare(strict_types=1);

namespace Lms\Domain\Service;

use DateTimeImmutable;
use Lms\Application\Port\CourseRepository;
use Lms\Application\Port\EnrollmentRepository;
use Lms\Domain\Enum\AccessDenialReason;
use Lms\Domain\Model\AccessDecision;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\StudentId;

final class AccessPolicy
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly EnrollmentRepository $enrollmentRepository
    ) {
    }

    public function decide(StudentId $studentId, CourseId $courseId, ContentId $contentId, DateTimeImmutable $at): AccessDecision
    {
        // 1. Check enrollment
        $enrollment = $this->enrollmentRepository->findActiveFor($studentId, $courseId, $at);
        if ($enrollment === null) {
            return AccessDecision::deny(AccessDenialReason::ENROLLMENT_NOT_ACTIVE);
        }

        // 2. Check course exists and has started
        $course = $this->courseRepository->get($courseId);
        if ($course === null) {
            return AccessDecision::deny(AccessDenialReason::CONTENT_NOT_AVAILABLE);
        }

        if (!$course->period->hasStartedAt($at)) {
            return AccessDecision::deny(AccessDenialReason::COURSE_NOT_STARTED);
        }

        // 3. Check content exists and is available
        $content = $course->findContent($contentId);
        if ($content === null) {
            return AccessDecision::deny(AccessDenialReason::CONTENT_NOT_AVAILABLE);
        }

        if (!$content->isAvailableAt($at, $course)) {
            return AccessDecision::deny(AccessDenialReason::CONTENT_NOT_AVAILABLE);
        }

        // 4. All checks passed
        return AccessDecision::allow();
    }
}

