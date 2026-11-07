-- ============================================================
-- ROLLBACK — hapus data uji REVISION (production)
-- Hanya menyentuh pengajuan bernomor SIK-2026-REV-001 / -002 beserta anak-anaknya.
-- ============================================================
SET @a1 := (SELECT id FROM permit_applications WHERE number = 'SIK-2026-REV-001');
SET @a2 := (SELECT id FROM permit_applications WHERE number = 'SIK-2026-REV-002');

DELETE FROM application_documents       WHERE permit_application_id IN (@a1, @a2);
DELETE FROM application_items           WHERE permit_application_id IN (@a1, @a2);
DELETE FROM application_personnel       WHERE permit_application_id IN (@a1, @a2);
DELETE FROM application_histories       WHERE permit_application_id IN (@a1, @a2);
DELETE FROM application_approval_steps  WHERE permit_application_id IN (@a1, @a2);
DELETE FROM permit_applications         WHERE id IN (@a1, @a2);

-- Berkas PDF uji ada di storage/app/private/sik/{id}/documents/ (hapus manual via File Manager bila perlu).
SELECT COUNT(*) AS sisa_pengajuan FROM permit_applications WHERE number LIKE 'SIK-2026-REV-%';
