<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Tambahkan ini
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffRole = Role::where('name', 'Staff')->first();
        if (!$staffRole) {
            $this->command->error('Role "Staff" not found. Please run RoleSeeder first.');
            return;
        }

        $positions = ['Kepala Tata Usaha', 'Staf Administrasi', 'Bendahara Sekolah', 'Operator Sekolah', 'Petugas Perpustakaan'];

        foreach ($positions as $position) {
            $user = User::factory()->create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
            ]);

            // Menggunakan DB facade untuk insert langsung ke tabel pivot
            DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_id' => $staffRole->id,
            ]);

            Staff::create([
                'user_id' => $user->id,
                'employee_id' => 'STF' . fake()->unique()->numerify('#####'),
                'date_of_birth' => fake()->date(),
                'position' => $position,
                'status' => 'active'
            ]);
        }
    }
}
