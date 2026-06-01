<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $candidate = User::factory()->create([
            'name' => 'Test Candidate',
            'email' => 'candidate@example.com',
            'password' => Hash::make('password'),
        ]);

        $alice = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
        ]);

        $bob = User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
        ]);

        Task::factory()->count(12)->create(['user_id' => $candidate->id]);
        Task::factory()->count(8)->create(['user_id' => $alice->id]);
        Task::factory()->count(5)->create(['user_id' => $bob->id]);
    }
}
