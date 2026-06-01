<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\UpdateTaskAction;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(UpdateTaskAction::class)]
final class UpdateTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function testUpdatesTaskFieldsAndReturnsRefreshed(): void
    {
        $task = Task::factory()->for(User::factory()->create())->create(['name' => 'Old name']);

        $updated = (new UpdateTaskAction)($task, ['name' => 'New name']);

        $this->assertInstanceOf(Task::class, $updated);
        $this->assertSame('New name', $updated->name);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'name' => 'New name']);
    }

    public function testUpdatesSingleField(): void
    {
        $task = Task::factory()->for(User::factory()->create())->create(['status' => 'todo']);

        $updated = (new UpdateTaskAction)($task, ['status' => 'done']);

        $this->assertSame('done', $updated->status->value);
    }
}
