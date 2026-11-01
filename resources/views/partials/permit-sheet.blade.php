@php
    /* Lembar SIK format resmi. Semua nilai diambil dari record pengajuan;
       teks baku (Dasar, Ketentuan, pejabat penandatangan) adalah isi templat
       dokumen, bukan data pemohon. Tembusan diambil dari alur persetujuan. */
    $app = $application;
    $periode = ($app->start_date?->translatedFormat('d F Y') ?? '……')
        . ' s.d. ' . ($app->end_date?->translatedFormat('d F Y') ?? '……');
    $areaName = $app->area?->name ?? '……';
    $workTypeName = $app->workType?->name;
    /* Uraian/tujuan pekerjaan (field `work_description` saja; `job_title`
       tidak lagi dipakai sebagai cadangan). */
    $workSubject = trim((string) ($app->work_description ?? '')) ?: '……';

    /* Sisi bandara menentukan instruksi kontak pada butir Ketentuan. Klasifikasi
       memakai KODE area (config sik.area_sides), bukan label yang bisa berubah. */
    $areaSide = $app->area?->side() ?? 'unknown';
    $kontakInstruksi = match ($areaSide) {
        'airside' => 'Airport Operation Center Head (AOCH) dan AMC Supervisor untuk wilayah sisi udara',
        'landside' => 'Airport Operation Center Head (AOCH) dan Terminal Landside Supervisor untuk wilayah sisi darat',
        default => 'Airport Operation Center Head (AOCH), AMC Supervisor untuk wilayah sisi udara, '
            . 'atau Terminal Landside Supervisor untuk wilayah sisi darat',
    };

    /* QR verifikasi: hanya untuk SIK yang sudah bernomor. Isinya URL halaman
       verifikasi publik; bukan tanda tangan digital tersertifikasi. */
    $verifyBase = rtrim((string) config('sik.verify_base'), '/');
    $qrUrl = filled($app->permit_number)
        ? $verifyBase . '/verifikasi/' . str_replace('/', '-', (string) $app->permit_number)
        : null;
    $qrSvg = $qrUrl ? \App\Support\ScanCode::dataUri($qrUrl) : null;

    /* Tembusan resmi berupa jabatan/departemen (config sik.tembusan), tanpa nama individu. */
    $tembusanDept = array_values(array_filter((array) config('sik.tembusan', [])));
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

    /* Daftar personel (nama + nomor HP + kendaraan) yang diizinkan masuk area keamanan terbatas.
       Kendaraan opsional: kolomnya hanya muncul bila ada baris yang mengisinya. */
    $personel = collect($app->personnel ?? [])->map(fn ($orang) => [
        'nama' => (string) $orang->name,
        'hp' => (string) ($orang->phone_number ?? ''),
        'kendaraan' => trim((string) ($orang->vehicle_plate ?? '')),
    ]);
    $adaKendaraan = $personel->contains(fn ($baris) => $baris['kendaraan'] !== '');

@endphp

<div class="sik-sheet" id="permitPreview">

    <header class="sik-kop">
        <div class="sik-kop-logo">
            {{-- Logo dokumen: InJourney Airports (bukan logo aplikasi SIK). --}}
            <img src="{{ $logoSrc ?? asset('images/injourney-airports.png') }}" alt="InJourney Airports">
        </div>
    </header>

    <h2 class="sik-title">SURAT IZIN KERJA</h2>
    <p class="sik-number">Nomor: {{ $app->permit_number ?? '……' }}</p>
    <p class="sik-about">TENTANG</p>
    <p class="sik-subject">{{ mb_strtoupper($workSubject) }}</p>

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
                Surat permohonan {{ $app->applicant_name ?? '……' }}@if ($app->submitted_at)
                    tanggal {{ $app->submitted_at->translatedFormat('d F Y') }}@endif perihal permohonan izin
                {{ $workSubject }}@if ($workTypeName) (jenis pekerjaan: {{ $workTypeName }})@endif
                di {{ $areaName }}.
            </td>
        </tr>
    </table>

    <p class="sik-allow">MENGIZINKAN</p>

    <table class="sik-rows">
        <tr>
            <th>Kepada:</th>
            <td>
                <strong>{{ $app->applicant_name ?? '……' }}</strong>
            </td>
        </tr>
        <tr>
            <th>Untuk:</th>
            <td>
                Izin melaksanakan pekerjaan <strong>{{ $workSubject }}</strong>
                di <strong>{{ $areaName }}</strong>@if ($workTypeName) — jenis pekerjaan <strong>{{ $workTypeName }}</strong>@endif:
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
                                @if ($adaKendaraan)
                                    <th>Kendaraan</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personel as $i => $orang)
                                <tr>
                                    <td class="sik-col-no">{{ $i + 1 }}</td>
                                    <td>{{ $orang['nama'] }}</td>
                                    <td>{{ $orang['hp'] ?: '—' }}</td>
                                    @if ($adaKendaraan)
                                        <td>{{ $orang['kendaraan'] }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif ($app->contact_name)
                    <table class="sik-table">
                        <thead><tr><th class="sik-col-no">No</th><th>Nama personel</th><th>No. HP</th></tr></thead>
                        <tbody><tr><td class="sik-col-no">1</td><td>{{ $app->contact_name }}</td><td>—</td></tr></tbody>
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
                    <li><strong>Tanggal/waktu:</strong> {{ $periode }} (sesuai jam operasional yang berlaku).
                        <strong>Lokasi:</strong> {{ $areaName }}.</li>
                    <li>Sebelum pelaksanaan pekerjaan agar menghubungi {{ $kontakInstruksi }};</li>
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
                @if ($qrSvg)
                    <div class="sik-qr">
                        <img src="{{ $qrSvg }}" alt="QR verifikasi dokumen {{ $app->permit_number }}">
                        <span>Pindai untuk verifikasi</span>
                    </div>
                @endif
                <div class="sik-sign-space"></div>
                @if ($signer && filled($signer->name))
                    <strong class="sik-signer-name">{{ mb_strtoupper($signer->name) }}</strong>
                @else
                    <span class="sik-signer-name sik-signer-empty">……………………………………</span>
                @endif
            </td>
        </tr>
    </table>

    @if (!empty($tembusanDept))
        <div class="sik-tembusan">
            <strong>Tembusan Yth.:</strong>
            <ul class="sik-tembusan-list">
                @foreach ($tembusanDept as $dept)
                    <li class="sik-tembusan-dept">{{ $dept }}</li>
                @endforeach
            </ul>
        </div>
    @endif

</div>
