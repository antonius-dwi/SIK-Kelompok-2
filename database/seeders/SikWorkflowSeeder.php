<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Models\WorkType;
use Illuminate\Database\Seeder;

/** Templat alur persetujuan awal (sama dengan frontend demo). */
class SikWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $area = Area::pluck('id', 'name');
        $type = WorkType::pluck('id', 'name');
        $user = User::pluck('id', 'username');

        $koordOps = ['Koordinator Operasi', 'approver2'];
        $mgrOps = ['Manajer Operasi Sisi Udara', 'approver'];
        $kepala = ['Kepala Bandara', 'approver5'];
        $koordTek = ['Koordinator Teknik', 'approver3'];
        $mgrTek = ['Manajer Teknik', 'approver4'];

        $templates = [
            ['Apron', 'Pemeliharaan', [$koordOps, $mgrOps, $kepala]],
            ['Taxiway', 'Konstruksi', [$koordTek, $mgrTek, $mgrOps, $kepala]],
            ['Taxiway', 'Pemeliharaan', [$koordTek, $mgrOps, $kepala]],
            ['Apron', 'Inspeksi', [$koordOps, $mgrOps]],
            ['Runway', 'Pemeliharaan', [$koordTek, $mgrTek, $kepala]],
            ['Terminal', 'Inspeksi', [$koordOps, $mgrOps]],
            ['Terminal', 'Pemeliharaan', [$koordTek, $mgrTek, $kepala]],
            ['Apron', 'Pembersihan', [$koordOps, $mgrOps]],
        ];

        foreach ($templates as [$areaName, $typeName, $steps]) {
            $template = WorkflowTemplate::updateOrCreate(
                ['area_id' => $area[$areaName], 'work_type_id' => $type[$typeName]],
                ['is_active' => true],
            );
            $template->steps()->delete();
            foreach ($steps as $i => [$title, $username]) {
                $template->steps()->create([
                    'stage' => $i + 1, 'approver_title' => $title, 'approver_user_id' => $user[$username],
                ]);
            }
        }
    }
}
