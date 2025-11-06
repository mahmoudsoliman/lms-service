<?php

declare(strict_types=1);

namespace Lms\Domain\Interfaces;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}

