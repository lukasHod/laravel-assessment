<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\TaskController;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

#[CoversClass(TaskController::class)]
final class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    // GET /tasks

    public function testIndexReturnsOnlyAuthenticatedUsersTasks(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::factory()->count(3)->for($userA)->create();
        Task::factory()->count(2)->for($userB)->create();

        $this->actingAs($userA, 'sanctum')
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function testIndexReturnsTasksInDescendingCreatedAtOrder(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['created_at' => now()->subMinutes(2)]);
        Task::factory()->for($user)->create(['created_at' => now()->subMinute()]);
        $newest = Task::factory()->for($user)->create(['created_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonPath('0.id', $newest->id);
    }

    public function testIndexRequiresAuthentication(): void
    {
        $this->getJson('/api/tasks')->assertStatus(401);
    }

    // POST /tasks

    public function testStoreCreatesTaskAndReturns201(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', [
                'name' => 'Write tests',
                'description' => 'Feature tests for all endpoints',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => '2026-12-31',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Write tests', 'status' => 'todo', 'priority' => 'high']);

        $this->assertDatabaseHas('tasks', ['name' => 'Write tests', 'user_id' => $user->id]);
    }

    public function testStoreAcceptsNullableFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', [
                'name' => 'Minimal Task',
                'status' => 'todo',
                'priority' => 'low',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Minimal Task', 'status' => 'todo', 'priority' => 'low']);
    }

    #[DataProvider('invalidStorePayloads')]
    public function testStoreValidationFailsWithInvalidPayload(array $payload, array $expectedErrors): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);
    }

    public static function invalidStorePayloads(): array
    {
        return [
            'missing name' => [
                ['status' => 'todo', 'priority' => 'medium'],
                ['name' => 'The name field is required.'],
            ],
            'invalid status value' => [
                ['name' => 'Task', 'status' => 'pending', 'priority' => 'medium'],
                ['status' => 'The selected status is invalid.'],
            ],
            'invalid priority value' => [
                ['name' => 'Task', 'status' => 'todo', 'priority' => 'urgent'],
                ['priority' => 'The selected priority is invalid.'],
            ],
            'invalid due_date format' => [
                ['name' => 'Task', 'status' => 'todo', 'priority' => 'medium', 'due_date' => 'not-a-date'],
                ['due_date' => 'The due date field must be a valid date.'],
            ],
        ];
    }

    public function testStoreRequiresAuthentication(): void
    {
        $this->postJson('/api/tasks', ['name' => 'Task', 'status' => 'todo', 'priority' => 'medium'])
            ->assertStatus(401);
    }

    // GET /tasks/{id}

    public function testShowReturnsTaskWith200(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $task->id, 'name' => $task->name]);
    }

    public function testShowReturns403ForAnotherUsersTask(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->actingAs($other, 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertStatus(403);
    }

    public function testShowReturns404ForNonExistentTask(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks/99999')
            ->assertStatus(404);
    }

    public function testShowRequiresAuthentication(): void
    {
        $this->getJson('/api/tasks/1')->assertStatus(401);
    }

    // PUT /tasks/{id}

    public function testUpdateModifiesTaskAndReturns200(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['name' => 'Old Name']);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'New Name']);
    }

    public function testUpdateCanChangeSingleField(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['status' => 'todo']);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonFragment(['status' => 'done']);
    }

    #[DataProvider('invalidUpdatePayloads')]
    public function testUpdateValidationFailsWithInvalidPayload(array $payload): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", $payload)
            ->assertStatus(422);
    }

    public static function invalidUpdatePayloads(): array
    {
        return [
            'invalid status' => [['status' => 'archived']],
            'invalid priority' => [['priority' => 'critical']],
            'invalid due_date' => [['due_date' => 'not-a-date']],
            'name empty string' => [['name' => '']],
        ];
    }

    public function testUpdateReturns403ForAnotherUsersTask(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->actingAs($other, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", ['name' => 'Hacked'])
            ->assertStatus(403);
    }

    public function testUpdateReturns404ForNonExistentTask(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/tasks/99999', ['name' => 'Test'])
            ->assertStatus(404);
    }

    public function testUpdateRequiresAuthentication(): void
    {
        $this->putJson('/api/tasks/1', ['name' => 'Test'])->assertStatus(401);
    }

    // DELETE /tasks/{id}

    public function testDestroyDeletesTaskAndReturns204(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function testDestroyReturns403ForAnotherUsersTask(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->actingAs($other, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function testDestroyReturns404ForNonExistentTask(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/tasks/99999')
            ->assertStatus(404);
    }

    public function testDestroyRequiresAuthentication(): void
    {
        $this->deleteJson('/api/tasks/1')->assertStatus(401);
    }
}
