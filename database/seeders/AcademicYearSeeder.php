<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel terlebih dahulu untuk memastikan data bersih
        DB::table('academic_years')->truncate();

        $this->command->info('Creating academic years...');

        // Siapkan data tahun ajaran
        $years = [
            [
                'year' => '2024/2025',
                'semester' => 'Ganjil',
                'start_date' => '2024-07-15',
                'end_date' => '2024-12-20',
                'status' => 'active', // Tahun ajaran ini yang akan digunakan oleh seeder lain
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => '2023/2024',
                'semester' => 'Genap',
                'start_date' => '2024-01-08',
                'end_date' => '2024-06-14',
                'status' => 'archived', // Contoh tahun ajaran yang sudah selesai
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => '2025/2026',
                'semester' => 'Ganjil',
                'start_date' => '2025-07-14',
                'end_date' => '2025-12-19',
                'status' => 'inactive', // Contoh tahun ajaran yang akan datang
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Masukkan data ke dalam database
        DB::table('academic_years')->insert($years);

        $this->command->info('Academic years created successfully.');
    }
}
