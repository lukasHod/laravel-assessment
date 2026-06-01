<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\ListUserTasksAction;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(ListUserTasksAction::class)]
final class ListUserTasksActionTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsOnlyGivenUsersTasks(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::factory()->count(3)->for($userA)->create();
        Task::factory()->count(2)->for($userB)->create();

        $tasks = (new ListUserTasksAction)($userA, null, []);

        $this->assertCount(3, $tasks);
        $tasks->each(fn ($task) => $this->assertSame($userA->id, $task->user_id));
    }

    public function testFiltersBySearchString(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['name' => 'Fix login bug']);
        Task::factory()->for($user)->create(['name' => 'Write tests']);
        Task::factory()->for($user)->create(['name' => 'Fix dashboard']);

        $tasks = (new ListUserTasksAction)($user, 'fix', []);

        $this->assertCount(2, $tasks);
    }

    public function testFiltersByStatusArray(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['status' => 'todo']);
        Task::factory()->for($user)->create(['status' => 'in_progress']);
        Task::factory()->for($user)->create(['status' => 'done']);

        $tasks = (new ListUserTasksAction)($user, null, ['todo', 'done']);

        $this->assertCount(2, $tasks);
    }

    public function testReturnsAllWhenNoFilters(): void
    {
        $user = User::factory()->create();

        Task::factory()->count(4)->for($user)->create();

        $tasks = (new ListUserTasksAction)($user, null, []);

        $this->assertCount(4, $tasks);
    }
}
