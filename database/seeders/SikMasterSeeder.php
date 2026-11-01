<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Department;
use App\Models\WorkType;
use Illuminate\Database\Seeder;

class SikMasterSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['OPS', 'Operasi Sisi Udara'], ['TEC', 'Teknik Bandara'], ['MGT', 'Manajemen']] as [$code, $name]) {
            Department::updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
        foreach ([['APN', 'Apron'], ['RWY', 'Runway'], ['TWY', 'Taxiway'], ['TER', 'Terminal'], ['HGR', 'Hanggar']] as [$code, $name]) {
            Area::updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
        foreach ([['MNT', 'Pemeliharaan'], ['CON', 'Konstruksi'], ['INS', 'Inspeksi'], ['CLN', 'Pembersihan'], ['OTH', 'Lainnya']] as [$code, $name]) {
            WorkType::updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
    }
}
