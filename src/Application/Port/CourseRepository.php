<?php

declare(strict_types=1);

namespace Lms\Application\Port;

use Lms\Domain\Model\Course;
use Lms\Domain\Model\Value\CourseId;

interface CourseRepository
{
    public function get(CourseId $id): ?Course;
}

