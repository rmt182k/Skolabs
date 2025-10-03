<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherAndSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Menggunakan transaction untuk memastikan semua proses berhasil atau tidak sama sekali
        DB::transaction(function () {
            // 1. Ambil data prasyarat (Role dan Subject)
            $teacherRole = Role::where('name', 'Teacher')->first();
            $subjects = Subject::all();

            if (!$teacherRole || $subjects->isEmpty()) {
                $this->command->error('Role "Teacher" or Subjects not found. Please run required seeders first.');
                return;
            }

            $this->command->info('Creating 15 new teachers...');

            // 2. Buat semua guru terlebih dahulu dan kumpulkan ID mereka
            // Ini lebih efisien daripada membuat satu per satu di dalam loop subjek
            $teachers = collect(); // Gunakan Laravel Collection untuk kemudahan
            for ($i = 0; $i < 15; $i++) {
                $user = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);

                // *** PERBAIKAN DI SINI ***
                // Kembali menggunakan DB facade untuk menghindari error jika relasi 'roles' tidak ada di model User
                DB::table('user_roles')->insert([
                    'user_id' => $user->id,
                    'role_id' => $teacherRole->id,
                ]);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'employee_id' => 'G' . fake()->unique()->numerify('#########'),
                    'date_of_birth' => fake()->date('Y-m-d', '1995-01-01'),
                    'phone_number' => '08' . fake()->unique()->numerify('##########'),
                    'address' => fake()->address(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'status' => 'active',
                ]);
                $teachers->push($teacher); // Tambahkan guru yang baru dibuat ke collection
            }

            $this->command->info('Assigning teachers to every subject...');
            $teacherIds = $teachers->pluck('id');
            $subjectTeacherData = [];

            // 3. FOKUS UTAMA: Iterasi setiap mata pelajaran (subject)
            // Ini adalah perubahan logika utama untuk memastikan tidak ada subjek yang kosong
            foreach ($subjects as $subject) {
                // Untuk setiap mata pelajaran, pilih 1 sampai 3 guru secara acak
                // Karena minimumnya 1, ini menjamin setiap subjek PASTI punya guru
                $numberOfTeachers = rand(1, 3);
                $assignedTeacherIds = $teacherIds->random($numberOfTeachers)->all();

                // Siapkan data untuk dimasukkan ke tabel pivot 'subject_teachers'
                foreach ($assignedTeacherIds as $teacherId) {
                    $subjectTeacherData[] = [
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacherId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // 4. Masukkan semua relasi ke tabel pivot dalam satu query untuk efisiensi
            if (!empty($subjectTeacherData)) {
                DB::table('subject_teachers')->insert($subjectTeacherData);
            }

            $this->command->info('Teacher and Subject Seeder has been completed successfully!');
        });
    }
}

