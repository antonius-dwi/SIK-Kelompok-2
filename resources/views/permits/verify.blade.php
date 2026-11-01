{{-- Halaman verifikasi publik dokumen SIK (tujuan QR pada surat). Tanpa data personel. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verifikasi SIK · {{ $permit?->permit_number ?? 'Tidak ditemukan' }}</title>
    <style>
        :root { --navy:#0A2A43; --cyan:#18B0B8; --ink:#12293D; --muted:#5C7186; --border:#DCE7EF; --bg:#F4F8FB; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:32px 18px;
               background:linear-gradient(180deg,#F4F8FB,#E9F2F7); color:var(--ink);
               font-family:"Segoe UI",system-ui,-apple-system,sans-serif; }
        .card { width:100%; max-width:640px; background:#fff; border:1px solid var(--border); border-radius:16px;
                box-shadow:0 18px 45px rgba(10,42,67,.10); overflow:hidden; }
        .head { padding:22px 26px; background:linear-gradient(160deg,#0F5C6B,var(--navy)); color:#fff; }
        .head small { display:block; letter-spacing:.14em; font-size:10.5px; font-weight:700; color:#7FD3DA; }
        .head h1 { margin:6px 0 0; font-size:18px; font-weight:700; }
        .body { padding:24px 26px 26px; }
        .badge { display:inline-flex; align-items:center; gap:7px; padding:6px 13px; border-radius:999px;
                 font-size:11.5px; font-weight:700; }
        .ok { background:#E7F6EF; color:#16805B; }
        .no { background:#FCEDEA; color:#C64C3C; }
        dl { display:grid; grid-template-columns:150px 1fr; gap:10px 16px; margin:20px 0 0; font-size:13px; }
        dt { color:var(--muted); font-weight:600; }
        dd { margin:0; font-weight:600; }
        .note { margin-top:22px; padding:13px 15px; border-radius:10px; background:var(--bg); border:1px solid var(--border);
                font-size:11.5px; line-height:1.6; color:var(--muted); }
        .code { font-family:ui-monospace,Consolas,monospace; font-size:12px; color:var(--navy); }
    </style>
</head>
<body>
<main class="card">
    <div class="head">
        <small>BANDAR UDARA INTERNASIONAL JENDERAL AHMAD YANI SEMARANG</small>
        <h1>Verifikasi Surat Izin Kerja (SIK)</h1>
    </div>
    <div class="body">
        @if ($permit)
            <span class="badge ok"><i>✓</i> Dokumen terdaftar dan telah diterbitkan</span>
            <dl>
                <dt>Nomor SIK</dt><dd class="code">{{ $permit->permit_number }}</dd>
                <dt>Pemohon</dt><dd>{{ $permit->applicant_name }}</dd>
                <dt>Area kerja</dt><dd>{{ $permit->area?->name ?? '—' }}</dd>
                <dt>Jenis pekerjaan</dt><dd>{{ $permit->workType?->name ?? '—' }}</dd>
                <dt>Periode</dt>
                <dd>
                    {{ $permit->start_date?->translatedFormat('d F Y') ?? '—' }}
                    s.d. {{ $permit->end_date?->translatedFormat('d F Y') ?? '—' }}
                </dd>
                <dt>Tanggal terbit</dt><dd>{{ $permit->issue_date?->translatedFormat('d F Y') ?? '—' }}</dd>
            </dl>
            <p class="note">
                Halaman ini hanya menampilkan data pokok dokumen. Kecocokan dengan lembar SIK yang
                dipegang petugas dapat diperiksa melalui nomor SIK di atas. Data personel tidak ditampilkan.
            </p>
        @else
            <span class="badge no"><i>!</i> Dokumen tidak ditemukan</span>
            <p class="note">
                Nomor <span class="code">{{ $token }}</span> tidak terdaftar sebagai SIK yang telah diterbitkan.
                Periksa kembali nomor pada lembar surat atau hubungi Airport Operation Center (AOC).
            </p>
        @endif
    </div>
</main>
</body>
</html>
