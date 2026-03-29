<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Skill;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create one admin user
        User::factory()->create([
            'u_name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'profile_pic' => null,
        ]);

        // Create one test user
        User::factory()->create([
            'u_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'profile_pic' => null,
        ]);

        // Generate 10 random users
        User::factory(10)->create();

        // Create some predefined skills
        $skills = [
            ['s_name' => 'Python', 'description' => 'Learn Python programming language'],
            ['s_name' => 'Java', 'description' => 'Java development for backend and Android'],
            ['s_name' => 'React', 'description' => 'Frontend development with ReactJS'],
            ['s_name' => 'Laravel', 'description' => 'Backend development with Laravel'],
            ['s_name' => 'Machine Learning', 'description' => 'ML fundamentals and advanced concepts'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['s_name' => $skill['s_name']], $skill);
        }

        // Generate 10 random extra skills
        Skill::factory(10)->create();
    }
}


