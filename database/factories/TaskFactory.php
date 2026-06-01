<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    private static array $taskNames = [
        'Set up CI/CD pipeline',
        'Fix login page validation',
        'Write unit tests for TaskController',
        'Update user profile page design',
        'Optimize database queries',
        'Add pagination to task list',
        'Implement password reset flow',
        'Create API documentation',
        'Fix mobile responsive layout',
        'Add email notification on task assignment',
        'Refactor authentication middleware',
        'Set up error monitoring (Sentry)',
        'Design onboarding flow',
        'Migrate to Laravel 12',
        'Add dark mode support',
        'Review pull request #42',
        'Fix timezone handling in reports',
        'Create data export feature',
        'Optimize image upload pipeline',
        'Add two-factor authentication',
        'Write integration tests for API',
        'Update dependencies to latest',
        'Fix caching issues on dashboard',
        'Implement search functionality',
        'Create admin panel',
        'Add file attachment to tasks',
        'Set up staging environment',
        'Write deployment documentation',
        'Fix N+1 query in task listing',
        'Add activity log to tasks',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement(self::$taskNames),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(TaskStatus::cases()),
            'priority' => $this->faker->randomElement(TaskPriority::cases()),
            'due_date' => $this->faker->optional(0.7)->dateTimeBetween('-1 week', '+1 month')?->format('Y-m-d'),
        ];
    }
}
