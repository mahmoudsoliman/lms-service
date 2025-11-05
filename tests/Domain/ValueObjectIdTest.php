<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\StudentId;
use PHPUnit\Framework\TestCase;

final class ValueObjectIdTest extends TestCase
{
    public function testStudentIdIsImmutable(): void
    {
        $id = new StudentId('student-123');
        $this->assertEquals('student-123', $id->value);
    }

    public function testStudentIdsAreEqualWhenSameValue(): void
    {
        $id1 = new StudentId('student-123');
        $id2 = new StudentId('student-123');
        $this->assertEquals($id1, $id2);
    }

    public function testCourseIdIsImmutable(): void
    {
        $id = new CourseId('course-456');
        $this->assertEquals('course-456', $id->value);
    }

    public function testCourseIdsAreEqualWhenSameValue(): void
    {
        $id1 = new CourseId('course-456');
        $id2 = new CourseId('course-456');
        $this->assertEquals($id1, $id2);
    }

    public function testContentIdIsImmutable(): void
    {
        $id = new ContentId('content-789');
        $this->assertEquals('content-789', $id->value);
    }

    public function testContentIdsAreEqualWhenSameValue(): void
    {
        $id1 = new ContentId('content-789');
        $id2 = new ContentId('content-789');
        $this->assertEquals($id1, $id2);
    }
}

