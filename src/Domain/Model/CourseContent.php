<?php

declare(strict_types=1);

namespace Lms\Domain\Model;

use DateTimeImmutable;

interface CourseContent
{
    public function isAvailableAt(DateTimeImmutable $at, Course $course): bool;
}

