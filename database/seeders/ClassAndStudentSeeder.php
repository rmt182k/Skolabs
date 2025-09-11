<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\EducationalLevel;
use App\Models\Major; // <-- Tambahkan ini
use App\Models\Role;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClassAndStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Classes::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $studentRole = Role::where('name', 'Student')->first();
        $teachers = Teacher::all();
        $levels = EducationalLevel::all(); // <-- PERUBAHAN 1: Hapus with('majors')
        $classSuffixes = ['A', 'B', 'C'];

        if ($studentRole === null || $teachers->isEmpty() || $levels->isEmpty()) {
            $this->command->error('Prerequisite data not found. Please run RoleSeeder, TeacherSeeder, and EducationalDataSeeder first.');
            return;
        }

        foreach ($levels as $level) {
            $gradeRange = [];
            if ($level->name == 'SD')
                $gradeRange = range(1, 6);
            if ($level->name == 'SMP')
                $gradeRange = range(7, 9);
            if (in_array($level->name, ['SMA', 'SMK']))
                $gradeRange = range(10, 12);

            foreach ($gradeRange as $grade) {
                // PERUBAHAN 2: Ambil jurusan secara manual untuk level saat ini
                $majorsForLevel = Major::where('educational_level_id', $level->id)->get();
                $majorsToLoop = $majorsForLevel->isEmpty() ? [null] : $majorsForLevel;

                foreach ($majorsToLoop as $major) {
                    foreach ($classSuffixes as $suffix) {
                        // Membuat nama kelas (e.g., "10 IPA A", "7 B")
                        $className = $grade . ' ';
                        if ($major) {
                            $className .= ($major->description ?? $major->name) . ' ';
                        }
                        $className .= $suffix;

                        // Membuat Kelas
                        $class = Classes::create([
                            'name' => $className,
                            'grade_level' => $grade,
                            'educational_level_id' => $level->id,
                            'major_id' => $major?->id,
                            'teacher_id' => $teachers->random()->id, // Wali kelas acak
                        ]);

                        // Membuat 30 siswa untuk kelas ini
                        for ($s = 0; $s < 30; $s++) {
                            $user = User::factory()->create([
                                'name' => fake()->name,
                                'email' => fake()->unique()->safeEmail,
                                'password' => Hash::make('password'),
                            ]);

                            DB::table('user_roles')->insert([
                                'user_id' => $user->id,
                                'role_id' => $studentRole->id
                            ]);

                            $student = Student::create([
                                'user_id' => $user->id,
                                'nisn' => fake()->unique()->numerify('##########'),
                                'date_of_birth' => fake()->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
                                'gender' => fake()->randomElement(['male', 'female']),
                                'phone_number' => fake()->numerify('08##########'),
                                'address' => fake()->address,
                                'enrollment_date' => fake()->dateTimeBetween('-12 years', 'now')->format('Y-m-d'),
                                'grade_level' => $grade,
                                'major_id' => $major?->id,
                                'status' => 'active',
                            ]);

                            DB::table('class_students')->insert([
                                'class_id' => $class->id,
                                'student_id' => $student->id
                            ]);
                        }
                    }
                }
            }
        }
    }
}

