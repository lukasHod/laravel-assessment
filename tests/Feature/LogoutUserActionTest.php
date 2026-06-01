<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\LogoutUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(LogoutUserAction::class)]
final class LogoutUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function testDeletesCurrentAccessToken(): void
    {
        $user = User::factory()->create();
        $user->createToken('auth_token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $token = $user->createToken('auth_token_2');
        $user->withAccessToken($token->accessToken);

        (new LogoutUserAction)($user);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}
