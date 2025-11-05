# LMS Core Domain

A fully-tested, framework-free PHP 8.2 implementation of a Learning Management System core domain following TDD and DDD principles.

## Setup

```bash
composer install
```

## Running Tests

```bash
composer test              # Run all tests
composer test:unit         # Run unit tests only
composer test:acceptance   # Run acceptance tests only
```

## Architecture

### Domain Layer
- **Value Objects**: StudentId, CourseId, ContentId, DateRange
- **Entities**: Course, Lesson, Homework, PrepMaterial, Enrollment
- **Domain Services**: AccessPolicy (decides access based on enrollment, course dates, and content availability)
- **Enums**: AccessDenialReason

### Application Layer
- **Ports**: Repository interfaces (CourseRepository, EnrollmentRepository)

### Infrastructure Layer
- **Repositories**: In-memory implementations for testing

## Access Control Rules

Access to course content is determined by the `AccessPolicy` domain service in the following order:

1. **ENROLLMENT_NOT_ACTIVE**: Student must be enrolled and enrollment period must be active (inclusive boundaries)
2. **COURSE_NOT_STARTED**: Course must have started (now >= course start)
3. **CONTENT_NOT_AVAILABLE**: Content must be available:
   - Lessons: available from scheduled datetime (inclusive)
   - Homework/Prep Materials: available from course start (inclusive)

If all checks pass, access is allowed.

## Boundary Conditions

- **DateRange**: Inclusive on both start and end (start <= at <= end)
- **Enrollment**: Valid when start <= at <= end
- **Course**: Started when now >= course start
- **Lessons**: Available when now >= scheduled datetime
- **Homework/Prep**: Available when now >= course start

## Example Usage

```php
use DateTimeImmutable;
use Lms\Domain\Service\AccessPolicy;
use Lms\Infrastructure\Persistence\InMemoryCourseRepository;
use Lms\Infrastructure\Persistence\InMemoryEnrollmentRepository;

$courseRepo = new InMemoryCourseRepository();
$enrollmentRepo = new InMemoryEnrollmentRepository();
$accessPolicy = new AccessPolicy($courseRepo, $enrollmentRepo);

// Create course, enroll student, check access...
$at = new DateTimeImmutable('2025-05-15 12:00:00');
$decision = $accessPolicy->decide(
    $studentId,
    $courseId,
    $contentId,
    $at
);
```

## Future HTTP Integration

When adding HTTP endpoints, you can wire the domain services directly to controllers. The `AccessPolicy` domain service returns `AccessDecision` objects which can be easily converted to JSON responses.

