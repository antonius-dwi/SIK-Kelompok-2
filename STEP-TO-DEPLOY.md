# STEP-TO-DEPLOY — SIK (Laravel 11) → InfinityFree

Runbook ini dibuat agar **model/agen lain** bisa men-deploy project `D:\hermes-project\SIK` ke
produksi tanpa mengulang riset. Ikuti urutannya; setiap langkah punya perintah, hasil yang
diharapkan, dan cara verifikasi.

- **Target produksi:** `https://sik-aviation.site.je`
- **Hosting:** InfinityFree (tanpa SSH / tanpa Composer di server / tanpa `exec()`)
- **Docroot:** `/htdocs/public` (kode aplikasi ada di `/htdocs`)
- **PHP produksi:** 8.4.27 · Laravel 11.57.0 · ext `gd`, `zip`, `mbstring`, `iconv` aktif
- **Batas produksi:** `memory_limit` 512M · `max_execution_time` 60 s · `upload_max_filesize` 20M
- **DB produksi:** `sql302.infinityfree.com` / `if0_43164138_sik_system` / user `if0_43164138`
- **Kredensial** (FTP + DB) ada di kepala `POSTDEPLOY-SIK-20261028\scripts\deploy_diff.py`
  (variabel `HOST/USER/PASS`) dan di `.env` produksi (`/htdocs/.env`). **Jangan** menyalinnya ke
  laporan/chat, dan **jangan pernah menimpa `/htdocs/.env`**.

---

## 0. Aturan wajib (jangan dilanggar)

1. **Jangan pernah** mengunggah/menimpa `/htdocs/.env`, `/htdocs/storage/*`, `/htdocs/vendor/laravel/*`
   kecuali memang ada perubahan dependensi (lihat §4).
2. **Backup DB sebelum migrasi** (§2). Tanpa backup, jangan jalankan `migrate`.
3. Semua berkas PHP sementara yang diunggah ke produksi **wajib**:
   - bernama `__*.php`, di `/htdocs/public/`,
   - dilindungi token (`?t=…`), dan
   - **dihapus** setelah dipakai (§7).
4. Hosting memakai WAF: `curl`/`python requests` ke domain produksi **sering ditolak**.
   Verifikasi & skrip PHP sementara dijalankan lewat **Edge headless** (§3).
5. Gambar (PNG/JPG) di produksi **di-re-encode** oleh hosting → jangan membandingkan sha256
   untuk aset gambar; bandingkan dimensi/isi (§6).
6. Jangan mengubah data produksi (pengajuan/akun) untuk keperluan uji.

---

## 1. Siapkan lingkungan lokal

```bash
# shell: git-bash di Windows
export PATH="/d/xampp/php:$PATH"

# 1a. MySQL lokal harus hidup (port 3306)
/d/xampp/mysql/bin/mysqladmin.exe -u root ping          # → "mysqld is alive"
# bila mati:
cd /d/xampp/mysql/bin && ./mysqld.exe --defaults-file=my.ini --console &

# 1b. Aplikasi lokal (untuk uji sebelum kirim)
cd /d/hermes-project/SIK && php artisan serve --host=127.0.0.1 --port=8000
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/login   # → 200

# 1c. Uji regresi
cd /d/hermes-project/SIK && php artisan test    # → minimal "2 passed"
```

Akun demo lokal: `applicant/applicant123`, `admin/admin123`, `approver/approver123`,
`approver2/operations123`, `approver5/director123`, `management/management123`,
`sysadmin/sysadmin123`.

---

## 2. Backup produksi (WAJIB sebelum migrasi)

Alat: `dbtool_template.php` (dump/drop/import/verify) di
`%LOCALAPPDATA%\hermes\cache\scratch\sik-check\`.

```bash
cd "$LOCALAPPDATA/hermes/cache/scratch/sik-check"
# siapkan skrip bertoken (token ada di file token2.txt)
python - <<'PY'
import ftplib, pathlib
PASS="<FTP_PASS>"; TOKEN=pathlib.Path('token2.txt').read_text().strip()
tpl=pathlib.Path('dbtool_template.php').read_text(encoding='utf-8')
pathlib.Path('dbtool_run.php').write_text(tpl.replace('__TOKEN__',TOKEN),encoding='utf-8',newline='\n')
ftp=ftplib.FTP("ftpupload.net",timeout=300); ftp.login("if0_43164138",PASS); ftp.set_pasv(True)
ftp.voidcmd("TYPE I")
with open('dbtool_run.php','rb') as fh: ftp.storbinary("STOR /htdocs/public/__dbtool.php", fh)
ftp.quit()
PY
# jalankan dump lewat Edge headless, lalu unduh file .sql dari /htdocs dan hapus dari server
```

Hasil yang diharapkan: JSON `{"ok":true,"file":"__backup-prod-YYYYmmdd-HHMMSS.sql","bytes":…,"tables":{…}}`
(± 21 tabel). Unduh via FTP ke `…\sik-check\prod-backup\`, lalu **hapus** `__backup-prod-*.sql`
dan `__dbtool.php` dari server.

Backup kode: `deploy_prod.py` menyimpan salinan berkas remote yang akan ditimpa (§3).

---

## 3. Pola menjalankan skrip PHP sementara di produksi (karena WAF)

```bash
EDGE="/c/Program Files (x86)/Microsoft/Edge/Application/msedge.exe"
PROF="$LOCALAPPDATA/hermes/cache/scratch/sik-check/edge-prod-$(date +%s)"
OUT=$(mktemp)
"$EDGE" --headless=new --disable-gpu --no-sandbox --user-data-dir="$PROF" \
        --virtual-time-budget=180000 --dump-dom \
        "https://sik-aviation.site.je/__nama.php?t=<TOKEN>" > "$OUT"
# JSON ada di dalam HTML → ekstrak
python -c "import re,json,sys;d=open(r'$OUT',encoding='utf-8',errors='replace').read();print(json.dumps(json.loads(re.search(r'\{.*\}',d,re.S).group(0)),indent=1))"
```

Selalu pakai `--user-data-dir` baru (profil Edge bisa korup) dan `--virtual-time-budget` besar
(±120–240 s) karena hosting lambat.

---

## 4. Deploy kode

### 4a. Deploy rutin (paling sering) — hanya berkas yang berubah

```bash
cd /d/hermes-project/POSTDEPLOY-SIK-20261028
python scripts/deploy_diff.py          # hash-diff, unggah yang beda, verifikasi ulang
```

Yang dilakukan skrip: bandingkan sha256 lokal vs remote untuk `app/ resources/ routes/ config/
bootstrap/ lang/ database/migrations/ public/ vendor/bacon vendor/dasprid vendor/composer`
+ `composer.json/lock`; **melewati** berkas gambar; unggah dengan `TYPE I` + blok 32 KB; lalu
verifikasi ulang hash. Output akhir harus: `verifikasi ulang: SEMUA COCOK`.

### 4b. Sinkron penuh (jarang) — 243 berkas + backup kode remote

```bash
cd /d/hermes-project/POSTDEPLOY-SIK-20261028
python scripts/deploy_prod.py "D:/hermes-project/BACKUP-SIK-prod-code-$(date +%Y%m%d-%H%M%S)"
```
± 8–12 menit. Berkas remote lama disimpan dulu ke folder backup tersebut.

### 4c. Bila `composer.json` berubah (dependensi baru)

Hosting tanpa Composer, jadi **vendor ikut diunggah**:
1. `php ../tools/composer.phar require <paket> --ignore-platform-reqs` (di `D:\hermes-project\SIK`).
2. Pastikan direktori `vendor/<vendor>/<paket>` masuk daftar `DIRS` di `deploy_diff.py`/`deploy_prod.py`.
3. `vendor/composer/*` (autoload) ikut diunggah — **wajib**, kalau tidak class baru tidak ketemu.
4. Setelah deploy, cek di probe: `class_exists` paket = `true`.

---

## 5. Migrasi database & cache (bila ada migrasi/config baru)

Template: `deploy_apply_template.php` (migrate `--force` + `optimize:clear` + `config:cache` +
potret keadaan). Unggah ke `/htdocs/public/__deploy.php`, jalankan via §3, lalu **hapus**.

Hasil yang diharapkan (contoh deploy 2026-10-29/30):
```
migrate: INFO Running migrations. | 2026_10_29_000002_add_work_description… DONE | 2026_10_29_000003_create_department_user_table DONE
bacon: true | zip: true | verify_base: https://sik-aviation.site.je | tembusan: 5
tabel department_user: True | baris: 15
kolom work_description: True
```

Penting:
- Produksi punya **config cache**. Setiap perubahan `config/*.php` atau `.env` baru berlaku
  setelah `optimize:clear` (atau `config:cache` ulang).
- Perubahan **Blade** tidak wajib clear (Laravel recompile dari mtime), tetapi tetap jalankan
  `view:clear` lewat `cache_clear_template.php` untuk memastikan.
- Jangan `migrate:fresh`, `migrate:reset`, atau `db:wipe`.

---

## 6. Verifikasi produksi (wajib, jangan diklaim tanpa bukti)

Skrip siap pakai di `POSTDEPLOY-SIK-20261028\scripts\` (butuh `node_modules` di folder itu):

```bash
cd /d/hermes-project/POSTDEPLOY-SIK-20261028
node scripts/verif_prod_deploy.js                 # login, sidebar, PDF, verifikasi, rekap, ZIP, pengguna
H=700 node scripts/verif_prod_modal.js            # modal pratinjau: satu scrollbar
H=950 node scripts/verif_prod_modal.js
python scripts/verify_prod_hashes.py              # opsional: audit hash seluruh berkas
```

Ekspektasi minimal:

| Cek | Nilai yang benar |
|---|---|
Login | `200`, banner login termuat (`naturalWidth` > 0) |
Sidebar | gambar banner `naturalWidth 900x302`, scrollbar sidebar `0` |
PDF (`/pemohon/pengajuan/{id}/sik/pdf`) | `200`, `application/pdf`, 1 halaman, ukuran ± 72 KB |
Isi surat | tidak ada `BANDAR UDARA`; ada `SURAT IZIN KERJA`; QR di antara `DIVISION HEAD` dan nama penandatangan |
QR | terbaca (mis. `zxing-cpp` pada render 150/200 dpi) → `https://sik-aviation.site.je/verifikasi/SIK-AYANI-YYYY-NNN` |
Halaman verifikasi | `200`, “terdaftar dan telah diterbitkan”, **tanpa** nomor HP |
Rekap | `/sistem/rekap-sik` `200`, baris = jumlah SIK terbit periode itu |
ZIP | `200`, `application/zip`, `content-disposition` nama `rekap-sik-*.zip` |
Pengguna/Peran | tombol Hapus ada, modal unit berupa checkbox |
Modal pratinjau | `bodyBisaScroll:false`, `scrollerDiModal:0`, scroll iframe sampai `posisi == maks`, `horizontal:0` |

Catatan: **gambar** di produksi di-re-encode hosting → `verify_prod_hashes.py` akan melaporkan
“beda” untuk PNG/JPG walau isinya sama. Bandingkan dimensi/isi (PIL) sebelum menyimpulkan rusak.

---

## 7. Bersih-bersih (wajib)

```python
import ftplib
ftp=ftplib.FTP("ftpupload.net",timeout=300); ftp.login("if0_43164138","<FTP_PASS>"); ftp.set_pasv(True)
sisa=[n for n,_ in ftp.mlsd("/htdocs/public") if n.startswith("__")]
print("sisa:", sisa)          # harus []
ftp.quit()
```
Hapus juga `__backup-prod-*.sql` di `/htdocs` bila sudah diunduh.

---

## 8. Rollback

| Kasus | Tindakan |
|---|---|
Kode salah | Salin balik berkas dari `BACKUP-SIK-prod-code-*` (hasil `deploy_prod.py`) atau `deploy_diff.py` ulang setelah `git`/snapshot lokal dipulihkan |
DB rusak | Pakai `dbtool_template.php` aksi `import` dengan file dump §2 (drop+import), atau `a=drop` lalu `a=import` |
Migrasi perlu dibatalkan | Buat skrip bertoken: `Artisan::call('migrate:rollback', ['--step'=>1,'--force'=>true])` — hanya bila `down()` tersedia dan sudah ada backup |
Cache aneh | `optimize:clear` + `config:cache` lewat template §5 |

---

## 9. Pitfall yang sudah pernah menggigit (jangan diulang)

| Gejala | Penyebab | Solusi |
|---|---|---|
PNG/JPG remote ~1,2× ukuran lokal, hash beda | Transfer FTP jatuh ke mode ASCII (konversi LF→CRLF) | Selalu `ftp.voidcmd("TYPE I")` sebelum `storbinary`, blok 32 KB, lalu **verifikasi hash/isi** |
Hash gambar beda walau tampil benar | Hosting re-encode gambar | Bandingkan piksel/dimensi, bukan hash |
`curl`/`requests` ke produksi → 000/403 | WAF menantang non-browser | Pakai Edge headless `--dump-dom` |
Skrip PHP sementara → 403 | Token salah/kadaluarsa | Ambil token dari `token2.txt`, sertakan `?t=` |
Halaman 500 setelah deploy config | `config:cache` masih memuat config lama | `optimize:clear` lalu `config:cache` |
PDF: kotak hitam di logo | Logo PNG alpha di DomPDF tanpa GD | Pakai JPEG berlatar putih (`public/images/injourney-airports.jpg`) |
PDF 2 halaman padahal isi cukup 1 | Elemen `page-break-inside: avoid` terdorong | Rapikan tinggi blok (QR 17 mm, `.sik-sign-space` 0) dan render ulang |
ZIP rekap gagal | `ext-zip` tidak ada / `max_execution_time` 60 s | Cek `class_exists('ZipArchive')`; batasi periode |
Modal pratinjau 2 scrollbar | `modal-dialog-scrollable` + iframe tinggi tetap | Badan modal `overflow:hidden`, iframe `height:100%` (§ deploy 2026-10-30) |

---

## 10. Peta berkas penting

```
D:\hermes-project\SIK\                       # aplikasi (aktif)
  app/Services/PermitPdfService.php          # render PDF (dipakai pemohon, admin, rekap)
  app/Support/ScanCode.php                   # QR SVG (bacon/bacon-qr-code)
  app/Http/Controllers/SystemAdmin/RecapController.php
  resources/views/partials/permit-sheet.blade.php   # satu-satunya template surat
  resources/views/partials/permit-modal.blade.php   # modal pratinjau
  config/sik.php                             # area_sides, tembusan, verify_base
  public/css/sik.css | sik-pdf.css           # layar | PDF (dibangun tools/gen_pdf_css.py)
D:\hermes-project\POSTDEPLOY-SIK-20261028\scripts\
  deploy_diff.py | deploy_prod.py | verify_prod_hashes.py
  verif_prod_deploy.js | verif_prod_modal.js
%LOCALAPPDATA%\hermes\cache\scratch\sik-check\
  dbtool_template.php | probe_deploy_template.php | deploy_apply_template.php | cache_clear_template.php
  token2.txt | prod-backup\*.sql
```

---

## 11. Checklist ringkas (salin-tempel)

```
[ ] MySQL + artisan serve lokal hidup, `php artisan test` hijau
[ ] Backup DB produksi (dump) tersimpan lokal
[ ] `python scripts/deploy_diff.py` → "SEMUA COCOK"
[ ] (bila ada migrasi) __deploy.php dijalankan → migrasi DONE, kelas/vendor terdeteksi
[ ] (bila config berubah) optimize:clear + config:cache
[ ] view:clear dijalankan
[ ] verif_prod_deploy.js hijau (login, PDF, QR, rekap+ZIP, pengguna)
[ ] verif_prod_modal.js hijau di 700 & 950 (satu scrollbar)
[ ] Semua `__*.php` & `__backup-prod-*.sql` dihapus dari server
[ ] Tidak ada perubahan pada .env / data pengajuan
```

---

## 12. Baseline produksi saat runbook ini dibuat (2026-10-30)

Gunakan sebagai pembanding kalau ada keanehan (drift):

| Item | Nilai |
|---|---|
PHP / Laravel | 8.4.27 / 11.57.0 |
Migrasi terakhir | `2026_10_29_000003_create_department_user_table` (10 migrasi terpasang) |
Data | 4 pengajuan (3 `ISSUED`: SIK/AYANI/2026/001–003, 1 `IN_APPROVAL`), 15 akun |
Tabel | 22 (21 + `department_user`), baris `department_user` = 15 |
Config | `verify_base` = `https://sik-aviation.site.je`, `tembusan` = 5 jabatan |
Vendor tambahan | `bacon/bacon-qr-code` ^3.0 (+`dasprid/enum`) |
Berkas sinkron | 243 (231 identik + 10 gambar yang di-re-encode hosting + 2 berkas terakhir) |
Ukuran PDF acuan | ± 71–72 KB, 1 halaman (SIK/AYANI/2026/002) |

---

## 13. Uji siklus penuh di produksi (tanpa menghapus data)

Skrip: `POSTDEPLOY-SIK-20261028\scriptsull_cycle_prod.js` (buat+kirim), lalu
`full_cycle_lanjut.js` / `full_cycle_selesai.js` untuk tahap persetujuan & penerbitan.

Pelajaran penting:
- **Klik tombol submit keputusan approver bisa memantul ke `/login`** (handler JS modal
  konfirmasi). Yang terbukti andal: kirim form keputusan lewat `fetch` same-origin + CSRF:
  `new FormData(form)` → `fetch(form.action, {method:'POST', credentials:'same-origin', redirect:'manual'})`.
- Ambil **id pengajuan** dari daftar `/pemohon` (link `/pemohon/pengajuan/{id}`), jangan dari
  teks nomor di halaman (bisa memuat nomor pengajuan lain).
- Setelah tiap tahap, cek `current_stage` lewat probe `__probe*.php` sebelum lanjut.
- Data uji **jangan dihapus** bila diminta menyimpan riwayat; catat id/nomor di laporan.
