<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\RegisterUserAction;
use App\Data\AuthResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RegisterUserAction::class)]
final class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatesUserAndReturnsAuthResult(): void
    {
        $result = (new RegisterUserAction)('Alice', 'alice@example.com', 'password123');

        $this->assertInstanceOf(AuthResult::class, $result);
        $this->assertSame('Alice', $result->user->name);
        $this->assertSame('alice@example.com', $result->user->email);
        $this->assertNotEmpty($result->token);
        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    public function testCreatesPersonalAccessToken(): void
    {
        $result = (new RegisterUserAction)('Alice', 'alice@example.com', 'password123');

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertSame($result->user->id, $result->user->tokens()->first()->tokenable_id);
    }
}
