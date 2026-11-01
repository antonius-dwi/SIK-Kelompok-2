{{-- $row: ['id','name','brand','model','sn','asset','qty','cond','move','freq','consumable','photo'] --}}
@php
    $cons = (bool) ($row['consumable'] ?? false);
    $cond = $row['cond'] ?? 'good';
    $move = $cons ? 'IN' : ($row['move'] ?? 'IN');
    $freq = $row['freq'] ?? 'One-time';
    $photo = $row['photo'] ?? null;
    // Indeks baris untuk menandai error validasi per baris (barang_nama.0, barang_qty.0, ...).
    // Error pada kunci induk ("barang_nama") ditandai di baris pertama.
    $idx = isset($loop) ? $loop->index : 0;
    $invalid = fn (string $key) => $errors->has("{$key}.{$idx}") || ($idx === 0 && $errors->has($key));
    $msg = fn (string $key) => $errors->first("{$key}.{$idx}") ?: ($idx === 0 ? $errors->first($key) : null);
@endphp
<tr class="equipment-row">
    <td>
        <input type="hidden" name="barang_id[]" value="{{ $row['id'] ?? '' }}">
        <input class="form-control form-control-sm @if ($invalid('barang_nama')) is-invalid @endif" name="barang_nama[]" aria-label="Nama barang" placeholder="cth. Bor listrik" value="{{ $row['name'] ?? '' }}" required>
        @if ($msg('barang_nama'))<div class="invalid-feedback">{{ $msg('barang_nama') }}</div>@endif
    </td>
    <td><div class="stacked-inputs">
        <input class="form-control form-control-sm" name="barang_brand[]" aria-label="Merek" placeholder="Merek" value="{{ $row['brand'] ?? '' }}">
        <input class="form-control form-control-sm" name="barang_model[]" aria-label="Tipe atau model" placeholder="Tipe / model" value="{{ $row['model'] ?? '' }}">
    </div></td>
    <td><div class="stacked-inputs">
        <input class="form-control form-control-sm" name="barang_sn[]" aria-label="Nomor seri" placeholder="No. seri" value="{{ $row['sn'] ?? '' }}">
        <input class="form-control form-control-sm" name="barang_asset_no[]" aria-label="Nomor aset" placeholder="No. aset" value="{{ $row['asset'] ?? '' }}">
    </div></td>
    <td><input class="form-control form-control-sm qty-input @if ($invalid('barang_qty')) is-invalid @endif" type="number" name="barang_qty[]" aria-label="Jumlah" min="1" value="{{ $row['qty'] ?? '' }}" required>@if ($msg('barang_qty'))<div class="invalid-feedback">{{ $msg('barang_qty') }}</div>@endif</td>
    <td>
        <select class="form-select form-select-sm @if ($invalid('barang_kondisi')) is-invalid @endif" name="barang_kondisi[]" aria-label="Kondisi">
            <option value="good" @selected($cond === 'good')>Baik</option>
            <option value="fair" @selected($cond === 'fair')>Cukup baik</option>
            <option value="needs_repair" @selected($cond === 'needs_repair')>Perlu perbaikan</option>
        </select>
    </td>
    <td>
        <select class="form-select form-select-sm @if ($invalid('barang_pergerakan')) is-invalid @endif" name="barang_pergerakan[]" aria-label="Arah pergerakan">
            <option value="IN" @selected($move === 'IN')>MASUK</option>
            <option value="OUT" @selected($move === 'OUT')>KELUAR</option>
            <option value="IN &amp; OUT" @selected($move === 'IN & OUT')>MASUK &amp; KELUAR</option>
        </select>
        <div class="form-check small consumable-check">
            <input type="hidden" name="barang_consumable[]" value="{{ $cons ? '1' : '0' }}">
            <label class="form-check-label"><input class="form-check-input" type="checkbox" data-consumable-toggle aria-label="Barang habis pakai" @checked($cons)><span>Habis pakai</span></label>
        </div>
    </td>
    <td>
        <select class="form-select form-select-sm @if ($invalid('barang_frekuensi')) is-invalid @endif" name="barang_frekuensi[]" aria-label="Frekuensi pergerakan">
            <option value="One-time" @selected($freq === 'One-time')>Satu kali</option>
            <option value="Daily" @selected($freq === 'Daily')>Harian</option>
            <option value="Weekly" @selected($freq === 'Weekly')>Mingguan</option>
            <option value="Other" @selected($freq === 'Other')>Lainnya</option>
        </select>
    </td>
    <td>
        <label class="photo-upload">
            <input class="photo-input @if ($invalid('barang_foto')) is-invalid @endif" type="file" name="barang_foto[]" accept="image/jpeg,image/png,image/webp" aria-label="Unggah foto barang">
            @if ($msg('barang_foto'))<div class="invalid-feedback">{{ $msg('barang_foto') }}</div>@endif
            <span class="photo-trigger" @if ($photo) hidden @endif><i class="fa-regular fa-image"></i> Unggah</span>
            <img class="photo-preview" alt="Pratinjau foto peralatan" @if ($photo) src="{{ $photo }}" @else hidden @endif>
        </label>
    </td>
    <td><button class="btn remove-row" type="button" aria-label="Hapus barang"><i class="fa-regular fa-trash-can"></i></button></td>
</tr>
