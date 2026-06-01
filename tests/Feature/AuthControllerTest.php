<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

#[CoversClass(AuthController::class)]
final class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRegisterCreatesUserAndReturnsTokenWith201(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    #[DataProvider('invalidRegisterPayloads')]
    public function testRegisterValidationFailsWithInvalidPayload(array $payload, array $expectedErrors): void
    {
        $this->postJson('/api/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);
    }

    public static function invalidRegisterPayloads(): array
    {
        $base = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        return [
            'missing name' => [
                array_diff_key($base, ['name' => '']),
                ['name' => 'The name field is required.'],
            ],
            'missing email' => [
                array_diff_key($base, ['email' => '']),
                ['email' => 'The email field is required.'],
            ],
            'invalid email format' => [
                array_merge($base, ['email' => 'not-an-email']),
                ['email' => 'The email field must be a valid email address.'],
            ],
            'missing password' => [
                ['name' => 'Alice', 'email' => 'alice@example.com'],
                ['password' => 'The password field is required.'],
            ],
            'password too short' => [
                array_merge($base, ['password' => 'short12', 'password_confirmation' => 'short12']),
                ['password' => 'The password field must be at least 8 characters.'],
            ],
            'password confirmation mismatch' => [
                array_merge($base, ['password_confirmation' => 'different123']),
                ['password' => 'The password field confirmation does not match.'],
            ],
            'missing password_confirmation' => [
                ['name' => 'Alice', 'email' => 'alice@example.com', 'password' => 'password123'],
                ['password' => 'The password field confirmation does not match.'],
            ],
        ];
    }

    public function testRegisterFailsWith422WhenEmailAlreadyTaken(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testLoginReturnsUserAndTokenWith200(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonFragment(['email' => $user->email]);
    }

    #[DataProvider('invalidLoginPayloads')]
    public function testLoginValidationFailsWithInvalidPayload(array $payload): void
    {
        $this->postJson('/api/login', $payload)
            ->assertStatus(422);
    }

    public static function invalidLoginPayloads(): array
    {
        return [
            'missing email' => [['password' => 'password123']],
            'invalid email format' => [['email' => 'not-an-email', 'password' => 'password123']],
            'missing password' => [['email' => 'test@example.com']],
        ];
    }

    public function testLoginFailsWith422ForWrongPassword(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testLoginFailsWith422ForNonExistentEmail(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testLogoutDeletesTokenAndReturns200(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function testLogoutRequiresAuthentication(): void
    {
        $this->postJson('/api/logout')->assertStatus(401);
    }
}
