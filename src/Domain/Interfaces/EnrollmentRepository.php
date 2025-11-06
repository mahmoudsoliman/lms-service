<?php

declare(strict_types=1);

namespace Lms\Domain\Interfaces;

use DateTimeImmutable;
use Lms\Domain\Model\Enrollment;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\StudentId;

interface EnrollmentRepository
{
    public function findActiveFor(StudentId $studentId, CourseId $courseId, DateTimeImmutable $at): ?Enrollment;

    public function save(Enrollment $enrollment): void;
}

