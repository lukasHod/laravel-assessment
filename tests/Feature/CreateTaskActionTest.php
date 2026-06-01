<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\CreateTaskAction;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(CreateTaskAction::class)]
final class CreateTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatesTaskForUserAndReturnsIt(): void
    {
        $user = User::factory()->create();

        $task = (new CreateTaskAction)($user, [
            'name' => 'Write tests',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertSame($user->id, $task->user_id);
        $this->assertSame('Write tests', $task->name);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'user_id' => $user->id]);
    }
}
