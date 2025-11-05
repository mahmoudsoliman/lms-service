<?php

declare(strict_types=1);

namespace Lms\Domain\Model;

use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use Lms\Domain\Model\Value\StudentId;

final class Enrollment
{
    public function __construct(
        public readonly StudentId $studentId,
        public readonly CourseId $courseId,
        public readonly DateRange $period
    ) {
    }
}

