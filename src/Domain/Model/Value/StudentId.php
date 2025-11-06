<?php

declare(strict_types=1);

namespace Lms\Domain\Model\Value;

final readonly class StudentId
{
    public function __construct(
        public string $value
    ) {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('StudentId cannot be empty');
        }
    }

    public function equals(StudentId $other): bool
    {
        return $this->value === $other->value;
    }
}

