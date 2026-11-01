{{-- $row: ['name','hp','plate'] --}}
@php
    // Indeks baris untuk menandai error validasi per baris (personel_nama.0, personel_hp.0, ...).
    // Bila array personel tidak dikirim sama sekali, error tercatat pada kunci induk ("personel_nama");
    // kasus itu ditandai pada baris pertama agar pengguna melihat field mana yang kurang.
    $idx = isset($loop) ? $loop->index : 0;
    $invalid = fn (string $key) => $errors->has("{$key}.{$idx}") || ($idx === 0 && $errors->has($key));
@endphp
<tr class="personnel-row">
    <td><input class="form-control form-control-sm @if ($invalid('personel_nama')) is-invalid @endif" name="personel_nama[]" aria-label="Nama personel" placeholder="Nama lengkap" value="{{ $row['name'] ?? '' }}" required></td>
    <td><input class="form-control form-control-sm @if ($invalid('personel_hp')) is-invalid @endif" name="personel_hp[]" type="tel" inputmode="tel" autocomplete="tel" maxlength="25" aria-label="Nomor HP" placeholder="Contoh: 081234567890" value="{{ $row['hp'] ?? '' }}" required></td>
    <td><input class="form-control form-control-sm @if ($invalid('personel_plat')) is-invalid @endif" name="personel_plat[]" aria-label="Nomor polisi kendaraan" placeholder="Opsional" value="{{ $row['plate'] ?? '' }}"></td>
    <td><button class="btn remove-row" type="button" aria-label="Hapus personel"><i class="fa-regular fa-trash-can"></i></button></td>
</tr>
