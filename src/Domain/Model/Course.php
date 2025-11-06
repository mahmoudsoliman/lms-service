<?php

declare(strict_types=1);

namespace Lms\Domain\Model;

use DateTimeImmutable;
use Lms\Domain\Model\Value\ContentId;
use Lms\Domain\Model\Value\CourseId;
use Lms\Domain\Model\Value\DateRange;

final class Course
{
    /** @var Lesson[] */
    private array $lessons = [];

    /** @var Homework[] */
    private array $homework = [];

    /** @var PrepMaterial[] */
    private array $prepMaterials = [];

    public function __construct(
        public readonly CourseId $id,
        public readonly string $title,
        public readonly DateRange $period
    ) {
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->period->start;
    }

    public function getEnd(): ?DateTimeImmutable
    {
        return $this->period->end;
    }

    public function addLesson(Lesson $lesson): void
    {
        $this->lessons[] = $lesson;
    }

    /** @return Lesson[] */
    public function getLessons(): array
    {
        return $this->lessons;
    }

    public function addHomework(Homework $homework): void
    {
        $this->homework[] = $homework;
    }

    /** @return Homework[] */
    public function getHomework(): array
    {
        return $this->homework;
    }

    public function addPrepMaterial(PrepMaterial $prepMaterial): void
    {
        $this->prepMaterials[] = $prepMaterial;
    }

    /** @return PrepMaterial[] */
    public function getPrepMaterials(): array
    {
        return $this->prepMaterials;
    }

    public function findContent(ContentId $contentId): ?CourseContent
    {
        foreach ($this->lessons as $lesson) {
            if ($lesson->id->equals($contentId)) {
                return $lesson;
            }
        }

        foreach ($this->homework as $hw) {
            if ($hw->id->equals($contentId)) {
                return $hw;
            }
        }

        foreach ($this->prepMaterials as $prep) {
            if ($prep->id->equals($contentId)) {
                return $prep;
            }
        }

        return null;
    }
}

