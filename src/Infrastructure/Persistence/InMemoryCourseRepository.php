<?php

declare(strict_types=1);

namespace Lms\Infrastructure\Persistence;

use Lms\Application\Port\CourseRepository;
use Lms\Domain\Model\Course;
use Lms\Domain\Model\Value\CourseId;

final class InMemoryCourseRepository implements CourseRepository
{
    /** @var array<string, Course> */
    private array $courses = [];

    public function get(CourseId $id): ?Course
    {
        return $this->courses[$id->value] ?? null;
    }

    public function save(Course $course): void
    {
        $this->courses[$course->id->value] = $course;
    }
}

