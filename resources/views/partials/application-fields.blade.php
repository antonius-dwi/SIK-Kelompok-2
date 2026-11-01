{{--
    Isi form pengajuan (dipakai form pemohon dan koreksi Admin).
    Variabel: $application (nullable), $areas, $workTypes.
    Peringatan templat hanya untuk form pemohon: $showTemplateWarning (bool)
    dan $templateCombos (daftar "areaId:workTypeId" bertemplat aktif).
    Form koreksi Admin tidak mengirim keduanya, jadi tidak ada peringatan di Admin.
    Mengisi ulang dari old() bila validasi gagal; selain itu dari data tersimpan.
--}}
@php
    $app = $application ?? null;
    $hasRequestLetter = $app?->documents->where('kind', 'request')->isNotEmpty() ?? false;
    $requestLetter = $app?->documents->firstWhere('kind', 'request');

    $savedItems = $app?->items->keyBy('id') ?? collect();
    $personnelRows = old('personel_nama') !== null
        ? collect(old('personel_nama'))->map(fn ($n, $i) => ['name' => $n, 'hp' => old("personel_hp.$i")])->values()
        : ($app?->personnel->map(fn ($p) => ['name' => $p->name, 'hp' => $p->phone_number])->values() ?? collect());
    if ($personnelRows->isEmpty()) { $personnelRows = collect([[]]); }

    $itemRows = old('barang_nama') !== null
        ? collect(old('barang_nama'))->map(function ($n, $i) use ($savedItems) {
            $saved = $savedItems->get((int) old("barang_id.$i"));
            return [
                'id' => old("barang_id.$i"), 'name' => $n, 'brand' => old("barang_brand.$i"), 'model' => old("barang_model.$i"),
                'sn' => old("barang_sn.$i"), 'asset' => old("barang_asset_no.$i"), 'qty' => old("barang_qty.$i"),
                'cond' => old("barang_kondisi.$i"), 'move' => old("barang_pergerakan.$i"), 'freq' => old("barang_frekuensi.$i"),
                'consumable' => old("barang_consumable.$i") === '1',
                'photo' => $saved?->photo_path ? route('items.photo', $saved) : null,
            ];
        })->values()
        : ($app?->items->map(fn ($it) => [
            'id' => $it->id, 'name' => $it->name, 'brand' => $it->brand, 'model' => $it->model, 'sn' => $it->serial_number,
            'asset' => $it->asset_number, 'qty' => $it->quantity, 'cond' => $it->condition, 'move' => $it->movement,
            'freq' => $it->frequency, 'consumable' => $it->is_consumable,
            'photo' => $it->photo_path ? route('items.photo', $it) : null,
        ])->values() ?? collect());
    if ($itemRows->isEmpty()) { $itemRows = collect([[]]); }

    $val = fn ($key, $saved) => old($key, $saved);

    // Peringatan kombinasi tanpa templat (server-rendered, untuk isian awal
    // / setelah validasi gagal; perubahan select ditangani sik.js).
    $selectedArea = old('area', $app?->area_id);
    $selectedWorkType = old('workType', $app?->work_type_id);
    $templateWarning = ($showTemplateWarning ?? false) && $selectedArea && $selectedWorkType
        && ! in_array($selectedArea . ':' . $selectedWorkType, $templateCombos ?? []);
@endphp

<section class="applicant-form-section" id="generalDetails">
    <div class="table-toolbar"><div><h3>Informasi pekerjaan</h3><p>Lengkapi data pemohon dan rencana pekerjaan.</p></div></div>
    <div class="general-details-grid">
        <label class="detail-field">Nama perusahaan / pemohon <span class="text-danger" aria-hidden="true">*</span><input class="form-control @error('applicantName') is-invalid @enderror" name="applicantName" placeholder="Nama perusahaan atau pemohon" autocomplete="organization" value="{{ $val('applicantName', $app?->applicant_name) }}" required>@error('applicantName')<div class="invalid-feedback">{{ $message }}</div>@enderror</label>
        <label class="detail-field">Nama penanggung jawab <span class="text-danger" aria-hidden="true">*</span><input class="form-control @error('contactName') is-invalid @enderror" name="contactName" placeholder="Nama penanggung jawab" autocomplete="name" value="{{ $val('contactName', $app?->contact_name ?? auth()->user()->name) }}" required>@error('contactName')<div class="invalid-feedback">{{ $message }}</div>@enderror</label>
        <label class="detail-field">Jabatan <span class="text-danger" aria-hidden="true">*</span><input class="form-control @error('jobTitle') is-invalid @enderror" name="jobTitle" placeholder="Contoh: Supervisor" value="{{ $val('jobTitle', $app?->job_title) }}" required>@error('jobTitle')<div class="invalid-feedback">{{ $message }}</div>@enderror</label>
        <label class="detail-field detail-field-wide">Uraian pekerjaan <span class="text-danger" aria-hidden="true">*</span><input class="form-control @error('workDescription') is-invalid @enderror" name="workDescription" placeholder="Contoh: Perbaikan marking apron dan pemasangan rambu keselamatan" value="{{ $val('workDescription', $app?->work_description) }}" maxlength="500" required>@error('workDescription')<div class="invalid-feedback">{{ $message }}</div>@enderror</label>
        <label class="detail-field">Area / lokasi <span class="text-danger" aria-hidden="true">*</span>
            <select class="form-select @error('area') is-invalid @enderror" name="area" required>
                <option value="">Pilih area kerja</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected((string) $val('area', $app?->area_id) === (string) $area->id)>{{ $area->name }}</option>
                @endforeach
            </select>
            @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </label>
        <label class="detail-field">Jenis pekerjaan <span class="text-danger" aria-hidden="true">*</span>
            <select class="form-select @error('workType') is-invalid @enderror" name="workType" required>
                <option value="">Pilih jenis pekerjaan</option>
                @foreach ($workTypes as $type)
                    <option value="{{ $type->id }}" @selected((string) $val('workType', $app?->work_type_id) === (string) $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            @error('workType')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </label>
        <label class="detail-field">Tanggal mulai <span class="text-danger" aria-hidden="true">*</span><input class="form-control @error('startDate') is-invalid @enderror" type="date" name="startDate" value="{{ $val('startDate', $app?->start_date?->toDateString()) }}" required>@error('startDate')<div class="invalid-feedback">{{ $message }}</div>@enderror</label>
        <label class="detail-field">Tanggal selesai <span class="text-danger" aria-hidden="true">*</span><input class="form-control @error('endDate') is-invalid @enderror" type="date" name="endDate" value="{{ $val('endDate', $app?->end_date?->toDateString()) }}" required>@error('endDate')<div class="invalid-feedback">{{ $message }}</div>@enderror</label>
    </div>
</section>

@if ($showTemplateWarning ?? false)
    <div id="templateWarning" class="alert alert-danger py-2" role="alert" data-combos='@json($templateCombos ?? [])' @if (! $templateWarning) hidden @endif>
        <strong>Perhatian:</strong> Kombinasi Area + Jenis pekerjaan ini belum memiliki templat alur kerja yang aktif, sehingga pengajuan tidak dapat dikirim ke Admin. Silakan pilih kombinasi lain atau hubungi System Admin. Draf tetap dapat disimpan.
    </div>
@endif

<section class="applicant-form-section" id="personnel">
    <div class="table-toolbar">
        <div><h3>Personel <span class="item-count" data-count-for="personnel" data-unit="personel"></span></h3><p>Daftarkan personel yang memerlukan akses.</p></div>
        <button class="btn btn-outline-primary btn-sm add-row-button" type="button" data-add-row="personnel"><i class="fa-solid fa-plus me-2"></i>Tambah personel</button>
    </div>
    <div class="table-responsive equipment-table-wrap">
        <table class="table equipment-table personnel-table">
            <thead><tr><th>NAMA PERSONEL <span class="text-danger" aria-hidden="true">*</span></th><th>NOMOR HP <span class="text-danger" aria-hidden="true">*</span></th><th><span class="visually-hidden">Tindakan</span></th></tr></thead>
            <tbody data-rows="personnel">
                @foreach ($personnelRows as $row)
                    @include('partials.personnel-row', ['row' => $row])
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="applicant-form-section" id="equipment">
    <div class="table-toolbar">
        <div><h3>Daftar peralatan <span class="item-count" data-count-for="item" data-unit="barang"></span></h3><p>Tambahkan setiap barang yang memerlukan izin keluar/masuk.</p></div>
        <button class="btn btn-outline-primary btn-sm add-row-button" type="button" data-add-row="item"><i class="fa-solid fa-plus me-2"></i>Tambah barang</button>
    </div>
    <div class="table-responsive equipment-table-wrap">
        <table class="table equipment-table">
            <thead><tr><th scope="col">NAMA BARANG <span class="text-danger" aria-hidden="true">*</span></th><th scope="col">MEREK / TIPE</th><th scope="col">NO. SERI / ASET</th><th scope="col">JUMLAH <span class="text-danger" aria-hidden="true">*</span></th><th scope="col">KONDISI</th><th scope="col">ARAH</th><th scope="col">FREKUENSI</th><th scope="col">FOTO</th><th scope="col"><span class="visually-hidden">Tindakan</span></th></tr></thead>
            <tbody data-rows="item">
                @foreach ($itemRows as $row)
                    @include('partials.item-row', ['row' => $row])
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="equipment-note"><i class="fa-solid fa-circle-info"></i><span>Unggah foto setiap barang dengan jelas. Format gambar yang diterima: JPG, PNG, atau WEBP.</span></div>
</section>

<section class="applicant-form-section" id="supportingDocuments">
    <div class="table-toolbar"><div><h3>Dokumen pendukung</h3><p>Surat permohonan dan dokumen pendukung wajib berformat PDF.</p></div></div>
    <div class="general-details-grid document-grid">
        <label class="detail-field">Surat permohonan (PDF) <span class="text-danger" aria-hidden="true">*</span>
            <input class="form-control @error('requestLetter') is-invalid @enderror" type="file" name="requestLetter" accept="application/pdf,.pdf" @required(! $hasRequestLetter)>
            @error('requestLetter')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <span class="field-hint">
                @if ($requestLetter)
                    Berkas saat ini: {{ $requestLetter->original_name }} (unggah berkas baru untuk mengganti).
                @else
                    Unggah surat permohonan resmi dalam format PDF.
                @endif
            </span>
        </label>
        <label class="detail-field">Lampiran (PDF)
            <input class="form-control @error('attachments.*') is-invalid @enderror" type="file" name="attachments[]" accept="application/pdf,.pdf" multiple>
            @error('attachments.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <span class="field-hint">Dapat memilih lebih dari satu dokumen. Mengunggah lampiran baru mengganti seluruh lampiran lama.</span>
        </label>
    </div>
    @if ($app && $app->documents->isNotEmpty())
        <div class="document-current-list mt-3">
            @foreach ($app->documents as $document)
                <a class="document-preview-link" href="{{ route('documents.show', $document) }}" target="_blank" rel="noopener">{{ $document->original_name }}</a>
            @endforeach
        </div>
    @endif
</section>

<template id="tpl-personnel">@include('partials.personnel-row', ['row' => []])</template>
<template id="tpl-item">@include('partials.item-row', ['row' => []])</template>
