<h1 align="center">SIK — Sistem Izin Kerja Sisi Udara</h1>
<p align="center">Aplikasi Laravel untuk pengajuan, verifikasi, persetujuan berjenjang, dan penerbitan <b>Surat Izin Kerja (SIK)</b> di area terbatas sisi udara Bandar Udara Internasional Jenderal Ahmad Yani, Semarang.</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-4479A1?logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

---

## Fitur

| Peran | Kemampuan |
|---|---|
| **Pemohon** | Buat pengajuan (pekerjaan, area, jenis pekerjaan, jadwal, personel, barang/peralatan, dokumen), simpan draf, kirim, perbaiki saat status Revisi, riwayat, unduh SIK terbit |
| **Admin / Verifier** | Antrean verifikasi, mulai pemeriksaan, teruskan ke persetujuan atau kembalikan untuk revisi, tangani koreksi dari approver, terbitkan SIK |
| **Approver** | Antrean persetujuan per tahap yang ditugaskan; Setujui / Kembalikan / Tolak (catatan wajib untuk pengembalian & penolakan) |
| **Management** | Monitoring seluruh pengajuan (hanya baca) |
| **System Administrator** | Pengguna, peran, departemen, area, jenis pekerjaan, templat alur persetujuan, audit log |

Alur status:

```
DRAFT → SUBMITTED → VERIFICATION ─┬→ IN_APPROVAL (tahap 1..N) → APPROVED → ISSUED
                                  └→ REVISION → SUBMITTED
IN_APPROVAL → CORRECTION (return approver) → IN_APPROVAL (tahap yang sama)
IN_APPROVAL → REJECTED
VERIFICATION / CORRECTION → CLOSED
```

Tahap persetujuan **berurutan** dan ditentukan oleh kombinasi **Area + Jenis pekerjaan** (templat alur). Semua transisi divalidasi di server (`app/Services/ApplicationWorkflowService.php`), bukan hanya di tampilan.

## Teknologi

- PHP 8.2+ (diuji pada PHP 8.4), **Laravel 11.57**
- MySQL / MariaDB, semua tabel `utf8mb4`
- Blade + Bootstrap 5.2 (CDN), CSS/JS aplikasi di `public/` (tanpa build step)
- `barryvdh/laravel-dompdf` (PDF SIK), `bacon/bacon-qr-code` (QR)

## Menjalankan secara lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
# sesuaikan DB_* pada .env, lalu:
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

## Akun demo (`database/seeders/SikUserSeeder.php`)

| Username | Password | Peran |
|---|---|---|
| `applicant` | `applicant123` | Pemohon |
| `admin` | `admin123` | Admin / Verifier |
| `approver` | `approver123` | Approver — Manajer Operasi Sisi Udara |
| `approver2` | `operations123` | Approver — Koordinator Operasi |
| `approver3` | `technical123` | Approver — Koordinator Teknik |
| `approver4` | `manager123` | Approver — Manajer Teknik |
| `approver5` | `director123` | Approver — Kepala Bandara |
| `management` | `management123` | Management |
| `sysadmin` | `sysadmin123` | System Administrator |

Kata sandi demo — ganti sebelum dipakai di lingkungan nyata.

## Struktur proyek

```
app/Enums/                  status pengajuan & peran
app/Http/Controllers/       Applicant, Admin, Approver, Management, SystemAdmin
app/Services/               ApplicationWorkflowService (seluruh transisi status),
                            ApplicationDataService, AuditLogger, NotificationService
app/Policies/               otorisasi per record
database/migrations/        skema
database/seeders/           master data, akun, templat alur
resources/views/            layout, halaman per peran, template dokumen SIK
routes/web.php              rute per peran
public/                     aset statis (CSS, JS, gambar)
```

## Produksi

- Domain: https://sik-aviation.site.je (hosting shared, tanpa SSH/Composer — `vendor/` disertakan)
- Deploy: unggah isi paket ke `htdocs`, jalankan `migrate --force` lalu `optimize:clear`; hapus `storage/framework/views/*` setelah mengubah Blade
- Dokumen SIK (PDF) dibuat di server dengan DomPDF

---

## Lisensi

MIT © SIK Kelompok 2
