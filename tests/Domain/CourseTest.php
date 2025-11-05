<?php

declare(strict_types=1);

namespace Lms\Tests\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Lms\Domain\Model\Course;
use Lms\Domain\Model\Homework;
use Lms\Domain\Model\Lesson;
use Lms\Domain\Model\PrepMaterial;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;
use PHPUnit\Framework\TestCase;

final class CourseTest extends TestCase
{
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Madrid');
    }

    public function testCourseCreation(): void
    {
        $courseId = new CourseId('course-1');
        $start = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $end = new DateTimeImmutable('2025-06-12 23:59:59', $this->timezone);
        $period = new DateRange($start, $end);

        $course = new Course($courseId, 'A-Level Biology', $period);

        $this->assertEquals($courseId, $course->id);
        $this->assertEquals('A-Level Biology', $course->title);
        $this->assertEquals($start, $course->getStart());
        $this->assertEquals($end, $course->getEnd());
    }

    public function testCourseWithNullEnd(): void
    {
        $courseId = new CourseId('course-1');
        $start = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($start, null);

        $course = new Course($courseId, 'Test Course', $period);

        $this->assertNull($course->getEnd());
    }

    public function testCourseCanHaveLessons(): void
    {
        $course = $this->createCourse();
        $lesson = new Lesson(
            new ContentId('lesson-1'),
            'Cell Structure',
            new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone)
        );

        $course->addLesson($lesson);

        $this->assertCount(1, $course->getLessons());
        $this->assertEquals($lesson, $course->getLessons()[0]);
    }

    public function testCourseCanHaveHomework(): void
    {
        $course = $this->createCourse();
        $homework = new Homework(
            new ContentId('homework-1'),
            'Label a Plant Cell'
        );

        $course->addHomework($homework);

        $this->assertCount(1, $course->getHomework());
        $this->assertEquals($homework, $course->getHomework()[0]);
    }

    public function testCourseCanHavePrepMaterials(): void
    {
        $course = $this->createCourse();
        $prep = new PrepMaterial(
            new ContentId('prep-1'),
            'Biology Reading Guide'
        );

        $course->addPrepMaterial($prep);

        $this->assertCount(1, $course->getPrepMaterials());
        $this->assertEquals($prep, $course->getPrepMaterials()[0]);
    }

    public function testCourseCanFindContentById(): void
    {
        $course = $this->createCourse();
        $lessonId = new ContentId('lesson-1');
        $homeworkId = new ContentId('homework-1');
        $prepId = new ContentId('prep-1');

        $lesson = new Lesson($lessonId, 'Cell Structure', new DateTimeImmutable('2025-05-15 10:00:00', $this->timezone));
        $homework = new Homework($homeworkId, 'Label a Plant Cell');
        $prep = new PrepMaterial($prepId, 'Biology Reading Guide');

        $course->addLesson($lesson);
        $course->addHomework($homework);
        $course->addPrepMaterial($prep);

        $this->assertEquals($lesson, $course->findContent($lessonId));
        $this->assertEquals($homework, $course->findContent($homeworkId));
        $this->assertEquals($prep, $course->findContent($prepId));
        $this->assertNull($course->findContent(new ContentId('non-existent')));
    }

    private function createCourse(): Course
    {
        $start = new DateTimeImmutable('2025-05-13 00:00:00', $this->timezone);
        $period = new DateRange($start, null);
        return new Course(
            new CourseId('course-1'),
            'Test Course',
            $period
        );
    }
}

