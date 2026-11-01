@php
    /* Lembar SIK format resmi. Semua nilai diambil dari record pengajuan;
       teks baku (Dasar, Ketentuan, pejabat penandatangan) adalah isi templat
       dokumen, bukan data pemohon. Tembusan diambil dari alur persetujuan. */
    $app = $application;
    $periode = ($app->start_date?->translatedFormat('d F Y') ?? '……')
        . ' s.d. ' . ($app->end_date?->translatedFormat('d F Y') ?? '……');
    $areaName = $app->area?->name ?? '……';
    $workTypeName = $app->workType?->name;
    $issuedOn = $app->issue_date?->translatedFormat('d F Y') ?? '……';

    $frekuensi = ['DAILY' => 'harian', 'WEEKLY' => 'mingguan', 'MONTHLY' => 'bulanan', 'ONCE' => 'sekali'];
    $arah = ['IN' => 'dimasukkan', 'OUT' => 'dikeluarkan'];

    /* Daftar barang/peralatan: acuan pemeriksaan Avsec (BR-31) — ditampilkan sebagai tabel. */
    $barang = collect($app->items ?? [])->map(function ($item) use ($frekuensi, $arah) {
        $spesifikasi = array_filter([
            $item->brand,
            $item->model,
            $item->serial_number ? 'SN ' . $item->serial_number : null,
            $item->asset_number ? 'Aset ' . $item->asset_number : null,
        ]);
        $ket = [];
        if (!empty($arah[strtoupper((string) $item->movement)])) $ket[] = $arah[strtoupper((string) $item->movement)];
        if (!empty($frekuensi[strtoupper((string) $item->frequency)])) $ket[] = 'frekuensi ' . $frekuensi[strtoupper((string) $item->frequency)];
        if ($item->is_consumable) $ket[] = 'habis pakai';
        return [
            'nama' => (string) $item->name,
            'spesifikasi' => implode(', ', $spesifikasi),
            'jumlah' => (int) $item->quantity,
            'keterangan' => implode('; ', $ket),
        ];
    });

    /* Daftar personel & kendaraan yang diizinkan masuk area keamanan terbatas. */
    $personel = collect($app->personnel ?? [])->map(fn ($orang) => [
        'nama' => (string) $orang->name,
        'hp' => (string) ($orang->phone_number ?? ''),
        'kendaraan' => (string) ($orang->vehicle_plate ?? ''),
    ]);

@endphp

<div class="sik-sheet" id="permitPreview">

    <header class="sik-kop">
        <div class="sik-kop-logo">
            <img src="{{ $logoSrc ?? asset('images/sik-logo-light.png') }}" alt="Logo SIK">
        </div>
        <div class="sik-kop-text">
            <strong>BANDAR UDARA INTERNASIONAL JENDERAL AHMAD YANI</strong>
            <span>SEMARANG</span>
            <small>Airport Operation, Services &amp; Security Division</small>
        </div>
    </header>

    <div class="sik-rule"></div>

    <h2 class="sik-title">SURAT IZIN KERJA</h2>
    <p class="sik-number">Nomor: {{ $app->permit_number ?? '……' }}</p>
    <p class="sik-about">TENTANG</p>
    <p class="sik-subject">{{ mb_strtoupper($app->job_title ?? '……') }}</p>

    <table class="sik-rows">
        <tr>
            <th>Dasar:</th>
            <td>
                <ol class="sik-list">
                    <li>Undang-Undang Republik Indonesia Nomor 1 Tahun 2009 tentang Penerbangan;</li>
                    <li>Peraturan Menteri Perhubungan Nomor PM 167 Tahun 2015 Perubahan Atas Peraturan Menteri
                        Perhubungan Nomor PM 33 Tahun 2015 tentang Pengendalian Jalan Masuk
                        (<em>Access Control</em>) ke Daerah Keamanan Terbatas di Bandar Udara;</li>
                    <li>SKEP/100/XI/1985 tentang Peraturan dan Tata Tertib Bandar Udara Bab XII.</li>
                </ol>
            </td>
        </tr>
        <tr>
            <th>Pertimbangan:</th>
            <td>
                Surat permohonan {{ $app->applicant_name ?? '……' }}@if ($app->contact_name) u.p. {{ $app->contact_name }}@endif
                tanggal {{ $app->submitted_at?->translatedFormat('d F Y') ?? '……' }} perihal permohonan izin
                {{ $app->job_title ?? '……' }}@if ($workTypeName) (jenis pekerjaan: {{ $workTypeName }})@endif
                di {{ $areaName }}.
            </td>
        </tr>
    </table>

    <p class="sik-allow">MENGIZINKAN</p>

    <table class="sik-rows">
        <tr>
            <th>Kepada:</th>
            <td>
                <strong>{{ $app->applicant_name ?? '……' }}</strong>@if ($app->contact_name)<br><span class="sik-muted">u.p. {{ $app->contact_name }}</span>@endif
            </td>
        </tr>
        <tr>
            <th>Untuk:</th>
            <td>
                Izin melaksanakan pekerjaan <strong>{{ $app->job_title ?? '……' }}</strong>
                di <strong>{{ $areaName }}</strong>@if ($workTypeName) — jenis pekerjaan <strong>{{ $workTypeName }}</strong>@endif,
                beserta barang/peralatan yang diizinkan keluar-masuk area keamanan terbatas:
                @if ($barang->isNotEmpty())
                    <table class="sik-table">
                        <thead>
                            <tr>
                                <th class="sik-col-no">No</th>
                                <th>Nama barang/peralatan</th>
                                <th>Merek/Model</th>
                                <th class="sik-col-jumlah">Jumlah</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($barang as $i => $b)
                                <tr>
                                    <td class="sik-col-no">{{ $i + 1 }}</td>
                                    <td>{{ $b['nama'] }}</td>
                                    <td>{{ $b['spesifikasi'] ?: '—' }}</td>
                                    <td class="sik-col-jumlah">{{ $b['jumlah'] }}</td>
                                    <td>{{ $b['keterangan'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="sik-empty">Tidak ada barang/peralatan yang dicatat pada pengajuan ini.</p>
                @endif
            </td>
        </tr>
        <tr>
            <th>Penanggung Jawab:</th>
            <td>
                @if ($personel->isNotEmpty())
                    <table class="sik-table">
                        <thead>
                            <tr>
                                <th class="sik-col-no">No</th>
                                <th>Nama personel</th>
                                <th>No. HP</th>
                                <th>No. kendaraan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personel as $i => $orang)
                                <tr>
                                    <td class="sik-col-no">{{ $i + 1 }}</td>
                                    <td>{{ $orang['nama'] }}</td>
                                    <td>{{ $orang['hp'] ?: '—' }}</td>
                                    <td>{{ $orang['kendaraan'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif ($app->contact_name)
                    <table class="sik-table">
                        <thead><tr><th class="sik-col-no">No</th><th>Nama personel</th><th>No. HP</th><th>No. kendaraan</th></tr></thead>
                        <tbody><tr><td class="sik-col-no">1</td><td>{{ $app->contact_name }}</td><td>—</td><td>—</td></tr></tbody>
                    </table>
                @else
                    <p class="sik-empty">—</p>
                @endif
            </td>
        </tr>
        <tr>
            <th>Ketentuan:</th>
            <td>
                <ol class="sik-list">
                    <li>Tanggal/waktu: {{ $periode }} (sesuai jam operasional yang disetujui). Lokasi: {{ $areaName }}.</li>
                    <li>Sebelum pelaksanaan pekerjaan agar menghubungi <em>Apron Movement Control</em> (AMC) On Duty
                        atau Airport Security Dept Head PT Angkasa Pura untuk dilakukan pendampingan dan pengawasan
                        agar sesuai spesifikasi standar mutu;</li>
                    <li>Melapor ke <em>Airport Operation Center Head</em> (AOCH);</li>
                    <li>Senantiasa menjaga kebersihan, ketertiban, dan keamanan selama pekerjaan dilaksanakan;</li>
                    <li>Para petugas harus menggunakan Pas Bandara atau ID Card yang berlaku;</li>
                    <li>Segala akibat dan risiko berkaitan dengan pekerjaan dimaksud menjadi beban dan tanggung jawab
                        pihak pemohon;</li>
                    <li>Mematuhi peraturan dan tata tertib yang berlaku di Bandar Udara Internasional Jenderal Ahmad
                        Yani Semarang.</li>
                </ol>
            </td>
        </tr>
    </table>

    <table class="sik-closing">
        <tr>
            <td class="sik-closing-place">
                Dikeluarkan di: Semarang<br>
                Pada tanggal: {{ $issuedOn }}
            </td>
            <td class="sik-closing-sign">
                <span class="sik-sign-title">a.n. GENERAL MANAGER</span>
                <span class="sik-sign-title">AIRPORT OPERATION, SERVICES &amp; SECURITY</span>
                <span class="sik-sign-title">DIVISION HEAD,</span>
                <div class="sik-sign-space"></div>
                @if ($signer && filled($signer->name))
                    <strong class="sik-signer-name">{{ mb_strtoupper($signer->name) }}</strong>
                @else
                    <span class="sik-signer-name sik-signer-empty">……………………………………</span>
                @endif
            </td>
        </tr>
    </table>

    @if ($tembusan->isNotEmpty())
        <div class="sik-tembusan">
            <strong>Tembusan Yth.:</strong>
            <ol class="sik-list sik-tembusan-list">
                @foreach ($tembusan as $t)
                    <li>
                        @if ($t['title'] !== '')
                            <span class="sik-tembusan-title">{{ $t['title'] }}</span>
                        @endif
                        @if ($t['title'] !== '' && $t['name'] !== '')<span class="sik-tembusan-sep"> — </span>@endif
                        @if ($t['name'] !== '')
                            <span class="sik-tembusan-name">{{ $t['name'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

</div>
