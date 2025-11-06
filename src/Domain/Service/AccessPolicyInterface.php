<?php

declare(strict_types=1);

namespace Lms\Domain\Service;

use DateTimeImmutable;
use Lms\Domain\Model\AccessDecision;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\StudentId;

interface AccessPolicyInterface
{
    public function decide(StudentId $studentId, CourseId $courseId, ContentId $contentId, DateTimeImmutable $at): AccessDecision;
}

