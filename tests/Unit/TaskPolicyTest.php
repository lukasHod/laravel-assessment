<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(TaskPolicy::class)]
final class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TaskPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TaskPolicy;
    }

    public function testViewAllowsOwner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->assertTrue($this->policy->view($user, $task));
    }

    public function testViewDeniesNonOwner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->assertFalse($this->policy->view($other, $task));
    }

    public function testUpdateAllowsOwner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->assertTrue($this->policy->update($user, $task));
    }

    public function testUpdateDeniesNonOwner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->assertFalse($this->policy->update($other, $task));
    }

    public function testDeleteAllowsOwner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->assertTrue($this->policy->delete($user, $task));
    }

    public function testDeleteDeniesNonOwner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->assertFalse($this->policy->delete($other, $task));
    }
}
