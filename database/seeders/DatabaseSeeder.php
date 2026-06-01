<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Test candidate account
        $candidate = User::factory()->create([
            'name' => 'Test Candidate',
            'email' => 'candidate@example.com',
            'password' => bcrypt('password'),
        ]);

        // Additional team members
        $alice = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
        ]);

        $bob = User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create tasks for each user
        Task::factory()->count(12)->create(['user_id' => $candidate->id]);
        Task::factory()->count(8)->create(['user_id' => $alice->id]);
        Task::factory()->count(5)->create(['user_id' => $bob->id]);
    }
}
