<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all user IDs to ensure the foreign key is valid
        $userIds = User::pluck('id');

        // Check if users exist to avoid foreign key constraint errors
        if ($userIds->isEmpty()) {
            $this->command->warn("No users found. Please seed users before leads.");
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            Lead::create([
                'title' => 'Sample Lead ' . ($i + 1),
                'assigned_user_id' => $userIds->random(), // Pick a random real user
                'client_id' => rand(100, 999), // Simulating the "Fake relation"
            ]);
        }
    }
}
