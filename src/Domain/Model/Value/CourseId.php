<?php

declare(strict_types=1);

namespace Lms\Domain\Model\Value;

final readonly class CourseId
{
    public function __construct(
        public string $value
    ) {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('CourseId cannot be empty');
        }
    }

    public function equals(CourseId $other): bool
    {
        return $this->value === $other->value;
    }
}

