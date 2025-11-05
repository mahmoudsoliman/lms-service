<?php

declare(strict_types=1);

namespace Lms\Infrastructure\Persistence;

use DateTimeImmutable;
use Lms\Application\Port\EnrollmentRepository;
use Lms\Domain\Model\Enrollment;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\StudentId;

final class InMemoryEnrollmentRepository implements EnrollmentRepository
{
    /** @var array<string, Enrollment> */
    private array $enrollments = [];

    public function findActiveFor(StudentId $studentId, CourseId $courseId, DateTimeImmutable $at): ?Enrollment
    {
        $key = $this->buildKey($studentId, $courseId);
        $enrollment = $this->enrollments[$key] ?? null;

        if ($enrollment === null) {
            return null;
        }

        if ($enrollment->period->contains($at)) {
            return $enrollment;
        }

        return null;
    }

    public function save(Enrollment $enrollment): void
    {
        $key = $this->buildKey($enrollment->studentId, $enrollment->courseId);
        $this->enrollments[$key] = $enrollment;
    }

    private function buildKey(StudentId $studentId, CourseId $courseId): string
    {
        return $studentId->value . '|' . $courseId->value;
    }
}

