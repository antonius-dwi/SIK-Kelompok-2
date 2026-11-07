<?php

namespace Database\Seeders;

use App\Models\ApplicationApprovalStep;
use App\Models\ApplicationDocument;
use App\Models\ApplicationHistory;
use App\Models\ApplicationItem;
use App\Models\ApplicationPersonnel;
use App\Models\PermitApplication;
use App\Models\User;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SikApproverRevisionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ===== REVISI #1: SIK-2026-REV-001 =====
        $app1 = PermitApplication::create([
            'number' => 'SIK-2026-REV-001',
            'user_id' => 1,
            'applicant_name' => 'PT. Garuda Wing Service',
            'contact_name' => 'Bambang Wijaya',
            'job_title' => 'Manajer Operasi',
            'area_id' => 1,
            'work_type_id' => 1,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
            'status' => ApplicationStatus::Revision->value,
            'current_stage' => 2,
            'total_stages' => 3,
            'correction_note' => 'Revisi dilakukan dari User Testing pada 01 Nov 2026 10:00 WIB. Perbaiki: (1) Tanggal mulai diperbaiki ke 1 November 2026, (2) Input nomor NIB diperbarui, (3) Lampiran Sertifikat Pengelola diperbarui.',
            'permit_number' => null,
            'issue_date' => null,
            'submitted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        ApplicationApprovalStep::create([
            'permit_application_id' => $app1->id,
            'stage' => 2,
            'approver_title' => 'Manajer Operasi Sisi Udara',
            'approver_user_id' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        ApplicationHistory::create([
            'permit_application_id' => $app1->id,
            'status' => ApplicationStatus::Revision->value,
            'stage' => 2,
            'actor_id' => 3,
            'actor_name' => 'Fadhlullah',
            'actor_role' => 'Approver',
            'decision' => 'RETURN',
            'comment' => 'Perlu direvisi: (1) Tanggal kerja dinilai belum akurat, (2) Jumlah personel kurang lengkap, (3) Item yang dilaporkan belum diverifikasi kondisi.',
            'created_at' => Carbon::createFromTime(10, 0, 0, 'Asia/Jakarta'),
        ]);

        ApplicationPersonnel::create(['permit_application_id' => $app1->id, 'name' => 'Bambang Wijaya', 'id_number' => '3175081990001234', 'vehicle_plate' => 'B 1234 XYZ']);
        ApplicationPersonnel::create(['permit_application_id' => $app1->id, 'name' => 'Agus Santoso', 'id_number' => '3175081985011234', 'vehicle_plate' => 'B 5678 ABC']);
        ApplicationItem::create(['permit_application_id' => $app1->id, 'name' => 'Alat Pengecatan Lange', 'brand' => 'Preco', 'model' => 'PC-2000', 'serial_number' => 'PC2K-2024-118', 'asset_number' => 'AP10001', 'quantity' => 1, 'condition' => 'good', 'movement' => 'IN', 'frequency' => 'One-time']);
        ApplicationItem::create(['permit_application_id' => $app1->id, 'name' => 'Bar Capacitor 1000V', 'brand' => 'Marx', 'model' => 'MPS-500', 'serial_number' => 'MRK5-2024-047', 'asset_number' => 'AP10002', 'quantity' => 3, 'condition' => 'fair', 'movement' => 'OUT', 'frequency' => 'Daily']);
        ApplicationDocument::create(['permit_application_id' => $app1->id, 'kind' => 'request', 'original_name' => 'SPPJP_2026_RVY.xlsx', 'path' => 'public/permits/sik-2026-rev-001.xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'size' => 20480, 'created_at' => $now, 'updated_at' => $now]);
        ApplicationDocument::create(['permit_application_id' => $app1->id, 'kind' => 'attachment', 'original_name' => 'SPPJP_2026_RVY.pdf', 'path' => 'public/permits/sik-2026-rev-001.pdf', 'mime' => 'application/pdf', 'size' => 153600, 'created_at' => $now, 'updated_at' => $now]);

        // ===== REVISI #2: SIK-2026-REV-002 =====
        $app2 = PermitApplication::create([
            'number' => 'SIK-2026-REV-002',
            'user_id' => 1,
            'applicant_name' => 'CV. Angkasa Maintenance',
            'contact_name' => 'Siti Rahayu',
            'job_title' => 'Koordinator Teknik',
            'area_id' => 2,
            'work_type_id' => 2,
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-14',
            'status' => ApplicationStatus::Revision->value,
            'current_stage' => 3,
            'total_stages' => 4,
            'correction_note' => 'Revisi dilakukan dari User Testing pada 01 Nov 2026 10:15 WIB. Perbaiki: (1) Area kerja diperjelas (area Werk 2), (2) Input Working Day diperbarui, (3) Dokumen Izin Penggunaan Persediaan diperbarui.',
            'permit_number' => null,
            'issue_date' => null,
            'submitted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        ApplicationApprovalStep::create([
            'permit_application_id' => $app2->id,
            'stage' => 3,
            'approver_title' => 'Manajer Teknik',
            'approver_user_id' => 6,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        ApplicationHistory::create([
            'permit_application_id' => $app2->id,
            'status' => ApplicationStatus::Revision->value,
            'stage' => 3,
            'actor_id' => 6,
            'actor_name' => 'Prido',
            'actor_role' => 'Approver',
            'decision' => 'RETURN',
            'comment' => 'Perlu direvisi: (1) Area kerja belum mencantumkan batas zona yang jelas, (2) Jumlah personel kurang lengkap, (3) Item yang dilaporkan belum diverifikasi kondisi.',
            'created_at' => Carbon::createFromTime(10, 15, 0, 'Asia/Jakarta'),
        ]);

        ApplicationPersonnel::create(['permit_application_id' => $app2->id, 'name' => 'Siti Rahayu', 'id_number' => '3175081988021234', 'vehicle_plate' => 'B 9012 JKL']);
        ApplicationPersonnel::create(['permit_application_id' => $app2->id, 'name' => 'Dewi Lestari', 'id_number' => '3175081989031234', 'vehicle_plate' => 'B 9013 MNO']);
        ApplicationItem::create(['permit_application_id' => $app2->id, 'name' => 'Jaket Protection', 'brand' => 'Flakjack', 'model' => 'FJ-300', 'serial_number' => 'FJ3K-2024-052', 'asset_number' => 'AP20001', 'quantity' => 5, 'condition' => 'good', 'movement' => 'IN', 'frequency' => 'One-time']);
        ApplicationItem::create(['permit_application_id' => $app2->id, 'name' => 'Sabun Pembersih Utama', 'brand' => 'CleanMax', 'model' => 'CM-100', 'serial_number' => 'CM1-2024-022', 'asset_number' => 'AP20002', 'quantity' => 2, 'condition' => 'fair', 'movement' => 'OUT', 'frequency' => 'Daily']);
        ApplicationDocument::create(['permit_application_id' => $app2->id, 'kind' => 'request', 'original_name' => 'SPPJP_2026_RVY2.xlsx', 'path' => 'public/permits/sik-2026-rev-002.xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'size' => 25600, 'created_at' => $now, 'updated_at' => $now]);
        ApplicationDocument::create(['permit_application_id' => $app2->id, 'kind' => 'attachment', 'original_name' => 'SPPJP_2026_RVY2.pdf', 'path' => 'public/permits/sik-2026-rev-002.pdf', 'mime' => 'application/pdf', 'size' => 192000, 'created_at' => $now, 'updated_at' => $now]);

        $this->command->info('Created 2 REVISION test data entries (SIK-2026-REV-001, SIK-2026-REV-002) on production.');
    }
}
