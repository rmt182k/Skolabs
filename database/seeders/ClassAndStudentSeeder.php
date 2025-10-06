<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\EducationalLevel;
use App\Models\Major;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject; // Ditambahkan
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
        DB::table('class_subjects')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =================================================================
        // === LOGIKA BARU UNTUK SUPER ADMIN DIMULAI DI SINI ===
        // =================================================================
        $this->command->info('Creating Super Admin users...');

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['description' => 'Peran dengan akses penuh ke semua fitur']
        );

        $user1 = User::factory()->create([
            'name' => 'Roy Martogi Tamba',
            'email' => 'roymartogit@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        DB::table('user_roles')->insert([
            'user_id' => $user1->id,
            'role_id' => $superAdminRole->id
        ]);

        $user2 = User::factory()->create([
            'name' => 'Muhammad Fakhran Hadyan',
            'email' => 'fakhran@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        DB::table('user_roles')->insert([
            'user_id' => $user2->id,
            'role_id' => $superAdminRole->id
        ]);
        // ========================================================
        // === LOGIKA BARU SUPER ADMIN BERAKHIR DI SINI ===
        // ========================================================

        // === PERUBAHAN 1: AMBIL ID TAHUN AJARAN AKTIF ===
        $activeYear = DB::table('academic_years')->where('status', 'active')->first();
        if (!$activeYear) {
            $this->command->error('No active academic year found. Please run AcademicYearSeeder first.');
            return;
        }
        $activeYearId = $activeYear->id;
        // =================================================

        $studentRole = Role::where('name', 'Student')->first();
        $teachers = Teacher::all();
        $levels = EducationalLevel::all();
        $classSuffixes = ['A', 'B', 'C'];

        $subjects = Subject::all()->keyBy('code');
        $subjectTeachers = DB::table('subject_teachers')->get()->groupBy('subject_id');

        if ($studentRole === null || $teachers->isEmpty() || $levels->isEmpty() || $subjects->isEmpty()) {
            $this->command->error('Prerequisite data not found. Please run Role, Teacher, Educational, and Subject seeders first.');
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
                $majorsForLevel = Major::where('educational_level_id', $level->id)->get();
                $majorsToLoop = $majorsForLevel->isEmpty() ? [null] : $majorsForLevel;

                foreach ($majorsToLoop as $major) {
                    foreach ($classSuffixes as $suffix) {
                        $className = $grade . ' ';
                        if ($major) {
                            $className .= ($major->description ?? $major->name) . ' ';
                        }
                        $className .= $suffix;

                        $class = Classes::create([
                            'name' => $className,
                            'grade_level' => $grade,
                            'educational_level_id' => $level->id,
                            'major_id' => $major?->id,
                            'teacher_id' => $teachers->random()->id,
                        ]);

                        // =================================================================
                        // === LOGIKA BARU UNTUK MENGISI `class_subjects` DIMULAI DI SINI ===
                        // =================================================================

                        $applicableSubjectCodes = ['PA', 'PPKN', 'BIN', 'MTK', 'SI', 'ENG', 'SB', 'PJOK'];
                        if (in_array($level->name, ['SMA', 'SMK'])) {
                            $applicableSubjectCodes[] = 'PKWU';
                        }
                        if ($level->name == 'SMA' && $major) {
                            if ($major->description == 'IPA') array_push($applicableSubjectCodes, 'FIS', 'KIM', 'BIO');
                            if ($major->description == 'IPS') array_push($applicableSubjectCodes, 'GEO', 'SOS', 'EKO');
                        }
                        if ($level->name == 'SMK' && $major) {
                            $smkMajorMap = ['AK' => 'DK-AK', 'TKJ' => 'DK-TKJ', 'RPL' => 'DK-RPL', 'TKR' => 'DK-TKR', 'MM' => 'DK-MM'];
                            if (isset($smkMajorMap[$major->description])) {
                                $applicableSubjectCodes[] = $smkMajorMap[$major->description];
                            }
                        }

                        foreach ($applicableSubjectCodes as $code) {
                            if (!isset($subjects[$code])) continue;

                            $subject = $subjects[$code];
                            $qualifiedTeachers = $subjectTeachers->get($subject->id);

                            if ($qualifiedTeachers && $qualifiedTeachers->isNotEmpty()) {
                                $teacherId = $qualifiedTeachers->random()->teacher_id;
                                DB::table('class_subjects')->insert([
                                    'class_id' => $class->id,
                                    'subject_id' => $subject->id,
                                    'teacher_id' => $teacherId,
                                    // === PERUBAHAN 2: TAMBAHKAN ACADEMIC_YEAR_ID ===
                                    'academic_year_id' => $activeYearId,
                                    // ===============================================
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                        // ========================================================
                        // === LOGIKA BARU BERAKHIR DI SINI ===
                        // ========================================================


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
