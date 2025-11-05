<?php

declare(strict_types=1);

namespace Lms\Domain\Model\Value;

final readonly class ContentId
{
    public function __construct(
        public string $value
    ) {
    }
}

