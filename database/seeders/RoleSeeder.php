<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Teacher', 'description' => 'Peran untuk Guru']);
        Role::create(['name' => 'Student', 'description' => 'Peran untuk Siswa']);
        Role::create(['name' => 'Staff', 'description' => 'Peran untuk Staf']);
    }
}
