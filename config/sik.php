<?php

/*
|--------------------------------------------------------------------------
| Pengaturan dokumen SIK
|--------------------------------------------------------------------------
|
| area_sides  : klasifikasi sisi bandara per KODE area (stabil, tidak berubah
|               saat label area diedit). Dipakai untuk instruksi kontak pada
|               butir Ketentuan surat.
| tembusan    : daftar jabatan/departemen penerima tembusan resmi, sesuai
|               urutan pada format dokumen (tanpa nama individu).
|
*/

return [

    'area_sides' => [
        'airside' => ['APN', 'RWY', 'TWY', 'HGR'],
        'landside' => ['TER'],
    ],

    // Basis URL publik untuk QR verifikasi (dokumen dicetak di domain ini).
    'verify_base' => env('SIK_VERIFY_BASE', 'https://sik-aviation.site.je'),

    'tembusan' => [
        'Airport Operation Center Head',
        'Non Aero Commercial Department Head',
        'Airport Quality & Safety Management System Department Head',
        'Airport Security Department Head',
        'Airport Operation Airside Department Head',
    ],

];
