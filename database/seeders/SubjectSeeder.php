<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['name' => 'Pendidikan Agama', 'code' => 'PA'],
            ['name' => 'Pendidikan Pancasila dan Kewarganegaraan', 'code' => 'PPKN'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Sejarah Indonesia', 'code' => 'SI'],
            ['name' => 'Bahasa Inggris', 'code' => 'ENG'],
            ['name' => 'Seni Budaya', 'code' => 'SB'],
            ['name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'code' => 'PJOK'],
            ['name' => 'Prakarya dan Kewirausahaan', 'code' => 'PKWU'],
            ['name' => 'Fisika', 'code' => 'FIS'],
            ['name' => 'Kimia', 'code' => 'KIM'],
            ['name' => 'Biologi', 'code' => 'BIO'],
            ['name' => 'Geografi', 'code' => 'GEO'],
            ['name' => 'Sosiologi', 'code' => 'SOS'],
            ['name' => 'Ekonomi', 'code' => 'EKO'],
            ['name' => 'Dasar Kejuruan TKJ', 'code' => 'DK-TKJ'],
            ['name' => 'Dasar Kejuruan RPL', 'code' => 'DK-RPL'],
            ['name' => 'Dasar Kejuruan AK', 'code' => 'DK-AK'],
            ['name' => 'Dasar Kejuruan TKR', 'code' => 'DK-TKR'],
            ['name' => 'Dasar Kejuruan MM', 'code' => 'DK-MM'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
