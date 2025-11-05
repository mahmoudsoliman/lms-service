<?php

declare(strict_types=1);

namespace Lms\Domain\Model;

use DateTimeImmutable;
use Lms\Domain\Model\Value\ContentId;

final class Lesson implements CourseContent
{
    public function __construct(
        public readonly ContentId $id,
        public readonly string $title,
        public readonly DateTimeImmutable $scheduledAt
    ) {
    }

    public function isAvailableAt(DateTimeImmutable $at, Course $course): bool
    {
        return $at >= $this->scheduledAt;
    }
}

