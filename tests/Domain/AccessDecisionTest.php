<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use Lms\Domain\Enum\AccessDenialReason;
use Lms\Domain\Model\AccessDecision;
use PHPUnit\Framework\TestCase;

final class AccessDecisionTest extends TestCase
{
    public function testAllowFactoryMethod(): void
    {
        $decision = AccessDecision::allow();

        $this->assertTrue($decision->allowed);
        $this->assertNull($decision->reason);
    }

    public function testDenyFactoryMethod(): void
    {
        $reason = AccessDenialReason::ENROLLMENT_NOT_ACTIVE;
        $decision = AccessDecision::deny($reason);

        $this->assertFalse($decision->allowed);
        $this->assertEquals($reason, $decision->reason);
    }

    public function testDenyWithDifferentReasons(): void
    {
        $reasons = [
            AccessDenialReason::ENROLLMENT_NOT_ACTIVE,
            AccessDenialReason::COURSE_NOT_STARTED,
            AccessDenialReason::CONTENT_NOT_AVAILABLE,
        ];

        foreach ($reasons as $reason) {
            $decision = AccessDecision::deny($reason);
            $this->assertFalse($decision->allowed);
            $this->assertEquals($reason, $decision->reason);
        }
    }
}

