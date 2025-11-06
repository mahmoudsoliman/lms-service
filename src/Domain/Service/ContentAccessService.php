<?php

declare(strict_types=1);

namespace Lms\Domain\Service;

use Lms\Domain\Interfaces\Clock;
use Lms\Domain\Interfaces\CourseRepository;
use Lms\Domain\Enum\AccessDenialReason;
use Lms\Domain\Exception\AccessDeniedException;
use Lms\Domain\Model\CourseContent;
use Lms\Domain\Model\Homework;
use Lms\Domain\Model\Lesson;
use Lms\Domain\Model\PrepMaterial;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\StudentId;

final class ContentAccessService
{
    public function __construct(
        private readonly AccessPolicyInterface $accessPolicy,
        private readonly CourseRepository $courseRepository,
        private readonly Clock $clock
    ) {
    }

    public function getContent(StudentId $studentId, CourseId $courseId, ContentId $contentId): CourseContent
    {
        $decision = $this->accessPolicy->decide($studentId, $courseId, $contentId);
        if (!$decision->allowed) {
            throw new AccessDeniedException($decision->reason);
        }

        $course = $this->courseRepository->get($courseId);
        if ($course === null) {
            throw new AccessDeniedException(AccessDenialReason::CONTENT_NOT_AVAILABLE, 'Course not found');
        }

        $content = $course->findContent($contentId);
        if ($content === null) {
            throw new AccessDeniedException(AccessDenialReason::CONTENT_NOT_AVAILABLE, 'Content not found');
        }

        return $content;
    }

    public function getLesson(StudentId $studentId, CourseId $courseId, ContentId $lessonId): Lesson
    {
        $content = $this->getContent($studentId, $courseId, $lessonId);

        if (!$content instanceof Lesson) {
            throw new AccessDeniedException(AccessDenialReason::CONTENT_NOT_AVAILABLE, 'Content is not a lesson');
        }

        return $content;
    }

    public function getHomework(StudentId $studentId, CourseId $courseId, ContentId $homeworkId): Homework
    {
        $content = $this->getContent($studentId, $courseId, $homeworkId);

        if (!$content instanceof Homework) {
            throw new AccessDeniedException(AccessDenialReason::CONTENT_NOT_AVAILABLE, 'Content is not homework');
        }

        return $content;
    }

    public function getPrepMaterial(StudentId $studentId, CourseId $courseId, ContentId $prepId): PrepMaterial
    {
        $content = $this->getContent($studentId, $courseId, $prepId);

        if (!$content instanceof PrepMaterial) {
            throw new AccessDeniedException(AccessDenialReason::CONTENT_NOT_AVAILABLE, 'Content is not prep material');
        }

        return $content;
    }
}

