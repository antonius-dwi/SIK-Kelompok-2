-- ============================================================
-- SIK REVISION TEST DATA - 2 entries from approver (Production)
-- Target: sik-aviation.site.je / if0_43164138_sik_system
-- Users: approver (id=3, Fadhlullah), approver4 (id=6, Prido)
-- ============================================================

-- ============================================================
-- INSERT - Application 1: SIK-2026-REV-001
-- ============================================================
START TRANSACTION;

INSERT INTO permit_applications (
    number, user_id, applicant_name, contact_name, job_title,
    area_id, work_type_id, start_date, end_date,
    status, current_stage, total_stages, correction_note,
    permit_number, issue_date, submitted_at, created_at, updated_at
) VALUES (
    'SIK-2026-REV-001', 1, 'PT. Garuda Wing Service', 'Bambang Wijaya', 'Manajer Operasi',
    1, 1, '2026-11-01', '2026-11-05',
    'REVISION', 2, 3,
    'Revisi dilakukan dari User Testing pada 01 Nov 2026 10:00 WIB. Perbaiki: (1) Tanggal mulai diperbaiki ke 1 November 2026, (2) Input nomor NIB diperbarui, (3) Lampiran Sertifikat Pengelola diperbarui.',
    NULL, NULL, '2026-11-01 10:00:00', '2026-11-01 10:00:00', '2026-11-01 10:00:00'
);

-- Approval step untuk REVISI #
INSERT INTO application_approval_steps (
    permit_application_id, stage, approver_title, approver_user_id, created_at, updated_at
) VALUES (
    LAST_INSERT_ID(), 2, 'Manajer Operasi Sisi Udara', 3, '2026-11-01 10:00:00', '2026-11-01 10:00:00'
);

-- History: RETURN oleh approver
INSERT INTO application_histories (
    permit_application_id, status, stage, actor_id, actor_name, actor_role,
    decision, comment, created_at
) VALUES (
    LAST_INSERT_ID(), 'REVISION', 2, 3, 'Fadhlullah', 'Approver',
    'RETURN',
    'Perlu direvisi: (1) Tanggal kerja dinilai belum akurat, (2) Jumlah personel kurang lengkap, (3) Item yang dilaporkan belum diverifikasi kondisi.',
    '2026-11-01 10:00:00'
);

-- Personnel
INSERT INTO application_personnel (permit_application_id, name, id_number, vehicle_plate, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'Bambang Wijaya', '3175081990001234', 'B 1234 XYZ', '2026-11-01 10:00:00', '2026-11-01 10:00:00');
INSERT INTO application_personnel (permit_application_id, name, id_number, vehicle_plate, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'Agus Santoso', '3175081985011234', 'B 5678 ABC', '2026-11-01 10:00:00', '2026-11-01 10:00:00');

-- Items
INSERT INTO application_items (permit_application_id, name, brand, model, serial_number, asset_number, quantity, condition, movement, frequency, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'Alat Pengecatan Lange', 'Preco', 'PC-2000', 'PC2K-2024-118', 'AP10001', 1, 'good', 'IN', 'One-time', '2026-11-01 10:00:00', '2026-11-01 10:00:00');
INSERT INTO application_items (permit_application_id, name, brand, model, serial_number, asset_number, quantity, condition, movement, frequency, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'Bar Capacitor 1000V', 'Marx', 'MPS-500', 'MRK5-2024-047', 'AP10002', 3, 'fair', 'OUT', 'Daily', '2026-11-01 10:00:00', '2026-11-01 10:00:00');

-- Documents
INSERT INTO application_documents (permit_application_id, kind, original_name, path, mime, size, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'request', 'SPPJP_2026_RVY.xlsx', 'public/permits/sik-2026-rev-001.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 20480, '2026-11-01 10:00:00', '2026-11-01 10:00:00');
INSERT INTO application_documents (permit_application_id, kind, original_name, path, mime, size, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'attachment', 'SPPJP_2026_RVY.pdf', 'public/permits/sik-2026-rev-001.pdf', 'application/pdf', 153600, '2026-11-01 10:00:00', '2026-11-01 10:00:00');

-- ============================================================
-- INSERT - Application 2: SIK-2026-REV-002
-- ============================================================
START TRANSACTION;

INSERT INTO permit_applications (
    number, user_id, applicant_name, contact_name, job_title,
    area_id, work_type_id, start_date, end_date,
    status, current_stage, total_stages, correction_note,
    permit_number, issue_date, submitted_at, created_at, updated_at
) VALUES (
    'SIK-2026-REV-002', 1, 'CV. Angkasa Maintenance', 'Siti Rahayu', 'Koordinator Teknik',
    2, 2, '2026-11-10', '2026-11-14',
    'REVISION', 3, 4,
    'Revisi dilakukan dari User Testing pada 01 Nov 2026 10:15 WIB. Perbaiki: (1) Area kerja diperjelas (area Werk 2), (2) Input Working Day diperbarui, (3) Dokumen Izin Penggunaan Persediaan diperbarui.',
    NULL, NULL, '2026-11-01 10:15:00', '2026-11-01 10:15:00', '2026-11-01 10:15:00'
);

INSERT INTO application_approval_steps (
    permit_application_id, stage, approver_title, approver_user_id, created_at, updated_at
) VALUES (
    LAST_INSERT_ID(), 3, 'Manajer Teknik', 6, '2026-11-01 10:15:00', '2026-11-01 10:15:00'
);

INSERT INTO application_histories (
    permit_application_id, status, stage, actor_id, actor_name, actor_role,
    decision, comment, created_at
) VALUES (
    LAST_INSERT_ID(), 'REVISION', 3, 6, 'Prido', 'Approver',
    'RETURN',
    'Perlu direvisi: (1) Area kerja belum mencantumkan batas zona yang jelas, (2) Jumlah personel kurang lengkap, (3) Item yang dilaporkan belum diverifikasi kondisi.',
    '2026-11-01 10:15:00'
);

INSERT INTO application_personnel (permit_application_id, name, id_number, vehicle_plate, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'Siti Rahayu', '3175081988021234', 'B 9012 JKL', '2026-11-01 10:15:00', '2026-11-01 10:15:00');
INSERT INTO application_personnel (permit_application_id, name, id_number, vehicle_plate, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'Dewi Lestari', '3175081989031234', 'B 9013 MNO', '2026-11-01 10:15:00', '2026-11-01 10:15:00');

INSERT INTO application_items (permit_application_id, name, brand, model, serial_number, asset_number, quantity, condition, movement, frequency, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'Jaket Protection', 'Flakjack', 'FJ-300', 'FJ3K-2024-052', 'AP20001', 5, 'good', 'IN', 'One-time', '2026-11-01 10:15:00', '2026-11-01 10:15:00');
INSERT INTO application_items (permit_application_id, name, brand, model, serial_number, asset_number, quantity, condition, movement, frequency, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'Sabun Pembersih Utama', 'CleanMax', 'CM-100', 'CM1-2024-022', 'AP20002', 2, 'fair', 'OUT', 'Daily', '2026-11-01 10:15:00', '2026-11-01 10:15:00');

INSERT INTO application_documents (permit_application_id, kind, original_name, path, mime, size, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'request', 'SPPJP_2026_RVY2.xlsx', 'public/permits/sik-2026-rev-002.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 25600, '2026-11-01 10:15:00', '2026-11-01 10:15:00');
INSERT INTO application_documents (permit_application_id, kind, original_name, path, mime, size, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 'attachment', 'SPPJP_2026_RVY2.pdf', 'public/permits/sik-2026-rev-002.pdf', 'application/pdf', 192000, '2026-11-01 10:15:00', '2026-11-01 10:15:00');

COMMIT;
