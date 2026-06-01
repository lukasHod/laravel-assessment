<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\DeleteTaskAction;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(DeleteTaskAction::class)]
final class DeleteTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function testRemovesTaskFromDatabase(): void
    {
        $task = Task::factory()->for(User::factory()->create())->create();

        (new DeleteTaskAction)($task);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
