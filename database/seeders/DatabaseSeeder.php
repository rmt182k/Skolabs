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
        $this->call([
            RoleSeeder::class,
            EducationalDataSeeder::class,
            AcademicYearSeeder::class, // <-- PANGGIL SEEDER BARU DI SINI
            SubjectSeeder::class,
            StaffSeeder::class,
            TeacherAndSubjectSeeder::class,
            ClassAndStudentSeeder::class,
        ]);
    }
}
