<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\PermitApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman verifikasi publik untuk dokumen SIK (tujuan QR pada surat).
 *
 * Sengaja hanya menampilkan data dokumen yang tidak sensitif: nomor SIK,
 * pemohon, area, jenis pekerjaan, periode, dan tanggal terbit. Nama personel,
 * nomor HP, dan catatan internal tidak pernah ditampilkan.
 */
class PermitVerificationController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $permit = PermitApplication::query()
            ->with(['area', 'workType'])
            ->where('status', ApplicationStatus::Issued->value)
            ->whereNotNull('permit_number')
            ->whereRaw("replace(permit_number, '/', '-') = ?", [$token])
            ->first();

        return view('permits.verify', [
            'permit' => $permit,
            'token' => $token,
        ]);
    }
}
