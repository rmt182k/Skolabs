<?php

namespace Database\Seeders;

use App\Models\EducationalLevel;
use App\Models\Major;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EducationalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Jenjang Pendidikan
        $sd = EducationalLevel::create(['name' => 'SD', 'duration_years' => 6, 'description' => 'Sekolah Dasar']);
        $smp = EducationalLevel::create(['name' => 'SMP', 'duration_years' => 3, 'description' => 'Sekolah Menengah Pertama']);
        $sma = EducationalLevel::create(['name' => 'SMA', 'duration_years' => 3, 'description' => 'Sekolah Menengah Atas']);
        $smk = EducationalLevel::create(['name' => 'SMK', 'duration_years' => 3, 'description' => 'Sekolah Menengah Kejuruan']);

        // Jurusan SMA
        Major::create(['educational_level_id' => $sma->id, 'name' => 'Ilmu Pengetahuan Alam', 'description' => 'IPA']);
        Major::create(['educational_level_id' => $sma->id, 'name' => 'Ilmu Pengetahuan Sosial', 'description' => 'IPS']);

        // Jurusan SMK
        Major::create(['educational_level_id' => $smk->id, 'name' => 'Akuntansi', 'description' => 'AK']);
        Major::create(['educational_level_id' => $smk->id, 'name' => 'Teknik Komputer dan Jaringan', 'description' => 'TKJ']);
        Major::create(['educational_level_id' => $smk->id, 'name' => 'Rekayasa Perangkat Lunak', 'description' => 'RPL']);
        Major::create(['educational_level_id' => $smk->id, 'name' => 'Teknik Kendaraan Ringan', 'description' => 'TKR']);
        Major::create(['educational_level_id' => $smk->id, 'name' => 'Multimedia', 'description' => 'MM']);
    }
}
