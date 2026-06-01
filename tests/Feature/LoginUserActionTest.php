<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\LoginUserAction;
use App\Data\AuthResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(LoginUserAction::class)]
final class LoginUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsAuthResultOnValidCredentials(): void
    {
        $user = User::factory()->create();

        $result = (new LoginUserAction)($user->email, 'password');

        $this->assertInstanceOf(AuthResult::class, $result);
        $this->assertSame($user->id, $result->user->id);
        $this->assertNotEmpty($result->token);
    }

    public function testThrowsValidationExceptionOnWrongPassword(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        (new LoginUserAction)($user->email, 'wrong-password');
    }

    public function testThrowsValidationExceptionForUnknownEmail(): void
    {
        $this->expectException(ValidationException::class);

        (new LoginUserAction)('nobody@example.com', 'password');
    }
}
