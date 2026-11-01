{{-- Rincian pengajuan hanya-baca (padanan buildApplicationDetails di sik.js). Butuh relasi: area, workType, personnel, items, documents. --}}
@php
    $conditions = ['good' => 'Baik', 'fair' => 'Cukup baik', 'needs_repair' => 'Perlu perbaikan'];
    $moves = ['IN' => 'MASUK', 'OUT' => 'KELUAR', 'IN & OUT' => 'MASUK & KELUAR'];
    $freqs = ['One-time' => 'Satu kali', 'Daily' => 'Harian', 'Weekly' => 'Mingguan', 'Other' => 'Lainnya'];
@endphp
<div class="review-detail-grid">
    <div><span class="context-label">PEMOHON / AREA</span><strong>{{ $application->applicant_name }} · {{ $application->area?->name ?? '—' }}</strong></div>
    <div><span class="context-label">PEKERJAAN / JENIS</span><strong>{{ $application->job_title }} · {{ $application->workType?->name ?? '—' }}</strong></div>
    <div><span class="context-label">PERIODE KERJA</span><strong>{{ $application->start_date?->translatedFormat('d M Y') ?? '—' }} – {{ $application->end_date?->translatedFormat('d M Y') ?? '—' }}</strong></div>
    <div><span class="context-label">PENANGGUNG JAWAB</span><strong>{{ $application->contact_name ?: '—' }}</strong></div>
    @php $totalTahap = (int) ($application->total_stages ?? 0); @endphp
    <div><span class="context-label">TAHAP</span><strong>@if ($totalTahap)Tahap {{ $application->current_stage ?: 1 }} dari {{ $application->total_stages }}@else Belum diteruskan @endif</strong></div>
    <div><span class="context-label">NOMOR PENGAJUAN</span><strong>{{ $application->number }}</strong></div>
</div>
<div class="review-readonly-list">
    <strong>Personel &amp; kendaraan</strong>
    @forelse ($application->personnel as $person)
        <span>{{ $person->name }} · HP {{ $person->phone_number ?: '—' }} · {{ $person->vehicle_plate ?: 'Tanpa kendaraan' }}</span>
    @empty
        <span>—</span>
    @endforelse
    <strong>Barang &amp; peralatan</strong>
    @forelse ($application->items as $item)
        <span>
            {{ $item->name }} · {{ trim($item->brand . ' ' . $item->model) ?: '—' }} · SN {{ $item->serial_number ?: '—' }} · aset {{ $item->asset_number ?: '—' }}
            · {{ $item->quantity }} unit · {{ $conditions[$item->condition] ?? $item->condition }} · {{ $moves[$item->movement] ?? $item->movement }} · {{ $freqs[$item->frequency] ?? $item->frequency }}{{ $item->is_consumable ? ' · Habis pakai' : '' }}
            @if ($item->photo_path) · <a href="{{ route('items.photo', $item) }}" target="_blank" rel="noopener">Foto</a>@endif
        </span>
    @empty
        <span>—</span>
    @endforelse
    <strong>Dokumen pendukung</strong>
    @forelse ($application->documents as $document)
        <a class="document-preview-link" href="{{ route('documents.show', $document) }}" target="_blank" rel="noopener">{{ $document->original_name }}</a>
    @empty
        <span>—</span>
    @endforelse
</div>
