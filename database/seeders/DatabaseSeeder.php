<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Roy Martogi Tamba',
            'email' => 'roymartogit@gmail.com',
            'email_verified_at' => now(),
        ]);
        $this->call([
            RoleSeeder::class,
            EducationalDataSeeder::class,
            SubjectSeeder::class,
            StaffSeeder::class,
            TeacherAndSubjectSeeder::class,
            ClassAndStudentSeeder::class,
        ]);
    }
}
