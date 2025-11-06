<?php

declare(strict_types=1);

namespace Lms\Domain\Model\Value;

final readonly class ContentId
{
    public function __construct(
        public string $value
    ) {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('ContentId cannot be empty');
        }
    }

    public function equals(ContentId $other): bool
    {
        return $this->value === $other->value;
    }
}

