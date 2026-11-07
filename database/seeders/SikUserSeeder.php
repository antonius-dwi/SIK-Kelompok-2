<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun contoh dari frontend demo. Hanya untuk development: kata sandi sama dengan di demo; ganti/hapus seeder ini sebelum production. Nama pada daftar ini adalah nama asli pemilik akun demo (lihat daftar akun login).
 */
class SikUserSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::pluck('id', 'code');

        $users = [
            ['applicant', 'applicant123', 'Aqil', Role::Applicant, 'OPS'],
            ['admin', 'admin123', 'Antonius Dwi', Role::Admin, 'OPS'],
            ['approver', 'approver123', 'Fadhlullah', Role::Approver, 'OPS'],      // Manajer Operasi Sisi Udara
            ['approver2', 'operations123', 'Afrizal', Role::Approver, 'OPS'],      // Koordinator Operasi
            ['approver3', 'technical123', 'Adhiansyah', Role::Approver, 'TEC'],    // Koordinator Teknik
            ['approver4', 'manager123', 'Prido', Role::Approver, 'TEC'],           // Manajer Teknik
            ['approver5', 'director123', 'Nur Aini', Role::Approver, 'MGT'],       // Kepala Bandara
            ['management', 'management123', 'Kusumawardhani', Role::Management, 'MGT'],
            ['sysadmin', 'sysadmin123', 'Muhammad Mustar', Role::SystemAdmin, 'MGT'],
        ];

        foreach ($users as [$username, $password, $name, $role, $deptCode]) {
            User::updateOrCreate(['username' => $username], [
                'name' => $name,
                'password' => $password, // di-hash otomatis oleh cast `hashed`
                'role' => $role,
                'department_id' => $dept[$deptCode],
                'is_active' => true,
            ]);
        }
    }
}
