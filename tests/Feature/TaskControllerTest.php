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

    public function test_index_returns_only_authenticated_users_tasks(): void
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

    public function test_index_returns_tasks_in_descending_created_at_order(): void
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

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/tasks')->assertStatus(401);
    }

    public function test_index_filters_tasks_by_name_search(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['name' => 'Fix login bug']);
        Task::factory()->for($user)->create(['name' => 'Write unit tests']);
        Task::factory()->for($user)->create(['name' => 'Fix dashboard crash']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks?search=fix')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Fix login bug'])
            ->assertJsonFragment(['name' => 'Fix dashboard crash']);
    }

    public function test_index_search_returns_empty_when_no_match(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['name' => 'Deploy to production']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks?search=xyz_no_match')
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_index_returns_all_tasks_when_search_is_empty(): void
    {
        $user = User::factory()->create();

        Task::factory()->count(3)->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks?search=')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_filters_by_single_status(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['status' => 'todo']);
        Task::factory()->for($user)->create(['status' => 'in_progress']);
        Task::factory()->for($user)->create(['status' => 'done']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks?status[]=todo')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['status' => 'todo']);
    }

    public function test_index_filters_by_multiple_statuses(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['status' => 'todo']);
        Task::factory()->for($user)->create(['status' => 'in_progress']);
        Task::factory()->for($user)->create(['status' => 'done']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks?status[]=todo&status[]=done')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['status' => 'todo'])
            ->assertJsonFragment(['status' => 'done']);
    }

    public function test_index_returns_all_tasks_when_no_status_filter(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['status' => 'todo']);
        Task::factory()->for($user)->create(['status' => 'in_progress']);
        Task::factory()->for($user)->create(['status' => 'done']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_rejects_invalid_status_value(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks?status[]=invalid_status')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status.0']);
    }

    public function test_index_combines_search_and_status_filters(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['name' => 'Fix login', 'status' => 'todo']);
        Task::factory()->for($user)->create(['name' => 'Fix signup', 'status' => 'done']);
        Task::factory()->for($user)->create(['name' => 'Fix crash', 'status' => 'in_progress']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks?search=fix&status[]=todo&status[]=done')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Fix login'])
            ->assertJsonFragment(['name' => 'Fix signup']);
    }

    // POST /tasks

    public function test_store_creates_task_and_returns201(): void
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

    public function test_store_accepts_nullable_fields(): void
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
    public function test_store_validation_fails_with_invalid_payload(array $payload, array $expectedErrors): void
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

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/tasks', ['name' => 'Task', 'status' => 'todo', 'priority' => 'medium'])
            ->assertStatus(401);
    }

    // GET /tasks/{id}

    public function test_show_returns_task_with200(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $task->id, 'name' => $task->name]);
    }

    public function test_show_returns403_for_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->actingAs($other, 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertStatus(403);
    }

    public function test_show_returns404_for_non_existent_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks/99999')
            ->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $this->getJson('/api/tasks/1')->assertStatus(401);
    }

    // PUT /tasks/{id}

    public function test_update_modifies_task_and_returns200(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['name' => 'Old Name']);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'New Name']);
    }

    public function test_update_can_change_single_field(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['status' => 'todo']);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonFragment(['status' => 'done']);
    }

    #[DataProvider('invalidUpdatePayloads')]
    public function test_update_validation_fails_with_invalid_payload(array $payload): void
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

    public function test_update_returns403_for_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->actingAs($other, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", ['name' => 'Hacked'])
            ->assertStatus(403);
    }

    public function test_update_returns404_for_non_existent_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/tasks/99999', ['name' => 'Test'])
            ->assertStatus(404);
    }

    public function test_update_requires_authentication(): void
    {
        $this->putJson('/api/tasks/1', ['name' => 'Test'])->assertStatus(401);
    }

    // DELETE /tasks/{id}

    public function test_destroy_deletes_task_and_returns204(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_destroy_returns403_for_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->actingAs($other, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_destroy_returns404_for_non_existent_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/tasks/99999')
            ->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $this->deleteJson('/api/tasks/1')->assertStatus(401);
    }
}
