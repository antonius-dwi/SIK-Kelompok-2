-- ============================================================
-- SIK — data uji status REVISION dari Approver (PRODUCTION)
-- Target : sik-aviation.site.je / if0_43164138_sik_system
-- Dibuat : 06 Nov 2026 (hasil uji live: app id 25, 26)
--
-- Skema mengikuti produksi: application_personnel.phone_number (bukan id_number),
-- application_items.is_consumable/photo_path, permit_applications.work_description.
-- Idempoten: baris dilewati bila nomor sudah ada.
-- ============================================================

-- ---------- REVISI #1 ----------
SET @app := (SELECT id FROM permit_applications WHERE number = 'SIK-2026-REV-001');

INSERT INTO permit_applications
    (number, user_id, applicant_name, contact_name, job_title, work_description,
     area_id, work_type_id, start_date, end_date, status, current_stage, total_stages,
     correction_note, submitted_at, created_at, updated_at)
SELECT 'SIK-2026-REV-001', 1, 'PT. Garuda Wing Service', 'Bambang Wijaya', 'Manajer Operasi',
       'Pengecatan ulang marka apron sisi udara',
       1, 1, '2026-11-01', '2026-11-05', 'REVISION', 2, 3,
       'Revisi dari Approver: (1) Tanggal mulai pekerjaan belum akurat, (2) Jumlah personel belum lengkap, (3) Kondisi item belum diverifikasi.',
       NOW(), NOW(), NOW()
WHERE @app IS NULL;
SET @app := (SELECT id FROM permit_applications WHERE number = 'SIK-2026-REV-001');

INSERT INTO application_approval_steps (permit_application_id, stage, approver_title, approver_user_id, created_at, updated_at)
VALUES (@app, 2, 'Manajer Operasi Sisi Udara', 3, NOW(), NOW());

INSERT INTO application_histories (permit_application_id, status, stage, actor_id, actor_name, actor_role, decision, comment, created_at)
VALUES (@app, 'REVISION', 2, 3, 'Fadhlullah', 'Approver', 'RETURN',
        'Perlu direvisi: (1) Tanggal kerja dinilai belum akurat, (2) Jumlah personel kurang lengkap, (3) Item yang dilaporkan belum diverifikasi kondisi.', NOW());

INSERT INTO application_personnel (permit_application_id, name, phone_number, vehicle_plate, created_at, updated_at) VALUES
  (@app, 'Bambang Wijaya', '081234567001', 'B 1234 XYZ', NOW(), NOW()),
  (@app, 'Agus Santoso',   '081234567002', 'B 5678 ABC', NOW(), NOW());

INSERT INTO application_items (permit_application_id, name, brand, model, serial_number, asset_number, quantity, `condition`, movement, frequency, is_consumable, created_at, updated_at) VALUES
  (@app, 'Alat Pengecatan Lange', 'Preco', 'PC-2000', 'PC2K-2024-118', 'AP10001', 1, 'good', 'IN',  'One-time', 0, NOW(), NOW()),
  (@app, 'Bar Capacitor 1000V',   'Marx',  'MPS-500', 'MRK5-2024-047', 'AP10002', 3, 'fair', 'OUT', 'Daily',    0, NOW(), NOW());

INSERT INTO application_documents (permit_application_id, kind, original_name, path, mime, size, created_at, updated_at) VALUES
  (@app, 'request',    'SPPJP_2026_RVY.xlsx', CONCAT('sik/', @app, '/documents/SPPJP_2026_RVY.xlsx'),
   'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 20480, NOW(), NOW()),
  (@app, 'attachment', 'SPPJP_2026_RVY.pdf',  CONCAT('sik/', @app, '/documents/SPPJP_2026_RVY.pdf'),
   'application/pdf', 153600, NOW(), NOW());

-- ---------- REVISI #2 ----------
SET @app := (SELECT id FROM permit_applications WHERE number = 'SIK-2026-REV-002');

INSERT INTO permit_applications
    (number, user_id, applicant_name, contact_name, job_title, work_description,
     area_id, work_type_id, start_date, end_date, status, current_stage, total_stages,
     correction_note, submitted_at, created_at, updated_at)
SELECT 'SIK-2026-REV-002', 1, 'CV. Angkasa Maintenance', 'Siti Rahayu', 'Koordinator Teknik',
       'Perbaikan penerangan runway sisi barat',
       2, 2, '2026-11-10', '2026-11-14', 'REVISION', 3, 4,
       'Revisi dari Approver: (1) Batas zona area kerja belum jelas, (2) Jumlah personel belum lengkap, (3) Kondisi item belum diverifikasi.',
       NOW(), NOW(), NOW()
WHERE @app IS NULL;
SET @app := (SELECT id FROM permit_applications WHERE number = 'SIK-2026-REV-002');

INSERT INTO application_approval_steps (permit_application_id, stage, approver_title, approver_user_id, created_at, updated_at)
VALUES (@app, 3, 'Manajer Teknik', 6, NOW(), NOW());

INSERT INTO application_histories (permit_application_id, status, stage, actor_id, actor_name, actor_role, decision, comment, created_at)
VALUES (@app, 'REVISION', 3, 6, 'Prido', 'Approver', 'RETURN',
        'Perlu direvisi: (1) Area kerja belum mencantumkan batas zona yang jelas, (2) Jumlah personel kurang lengkap, (3) Item yang dilaporkan belum diverifikasi kondisi.', NOW());

INSERT INTO application_personnel (permit_application_id, name, phone_number, vehicle_plate, created_at, updated_at) VALUES
  (@app, 'Siti Rahayu',  '081234567003', 'B 9012 JKL', NOW(), NOW()),
  (@app, 'Dewi Lestari', '081234567004', 'B 9013 MNO', NOW(), NOW());

INSERT INTO application_items (permit_application_id, name, brand, model, serial_number, asset_number, quantity, `condition`, movement, frequency, is_consumable, created_at, updated_at) VALUES
  (@app, 'Jaket Protection',        'Flakjack', 'FJ-300',  'FJ3K-2024-052', 'AP20001', 5, 'good', 'IN',  'One-time', 0, NOW(), NOW()),
  (@app, 'Sabun Pembersih Utama',   'CleanMax', 'CM-100',  'CM1-2024-022',  'AP20002', 2, 'fair', 'OUT', 'Daily',    1, NOW(), NOW());

INSERT INTO application_documents (permit_application_id, kind, original_name, path, mime, size, created_at, updated_at) VALUES
  (@app, 'request',    'SPPJP_2026_RVY2.xlsx', CONCAT('sik/', @app, '/documents/SPPJP_2026_RVY2.xlsx'),
   'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 25600, NOW(), NOW()),
  (@app, 'attachment', 'SPPJP_2026_RVY2.pdf',  CONCAT('sik/', @app, '/documents/SPPJP_2026_RVY2.pdf'),
   'application/pdf', 192000, NOW(), NOW());
