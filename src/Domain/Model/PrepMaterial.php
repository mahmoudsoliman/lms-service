<?php

declare(strict_types=1);

namespace Lms\Domain\Model;

use DateTimeImmutable;
use Lms\Domain\Model\Value\ContentId;

final class PrepMaterial implements CourseContent
{
    public function __construct(
        public readonly ContentId $id,
        public readonly string $title
    ) {
    }

    public function isAvailableAt(DateTimeImmutable $at, Course $course): bool
    {
        return $course->period->hasStartedAt($at);
    }
}

