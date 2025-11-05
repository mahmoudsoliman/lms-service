<?php

declare(strict_types=1);

namespace Lms\Domain\Enum;

enum AccessDenialReason: string
{
    case ENROLLMENT_NOT_ACTIVE = 'ENROLLMENT_NOT_ACTIVE';
    case COURSE_NOT_STARTED = 'COURSE_NOT_STARTED';
    case CONTENT_NOT_AVAILABLE = 'CONTENT_NOT_AVAILABLE';
}

