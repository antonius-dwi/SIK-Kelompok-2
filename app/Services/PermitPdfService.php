<?php

namespace App\Services;

use App\Models\PermitApplication;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Sumber tunggal data + berkas PDF surat SIK.
 *
 * Dipakai oleh jalur pemohon/admin (pratinjau, cetak, unduh) dan oleh menu
 * Rekap SIK, supaya dokumen yang diunduh dari mana pun identik.
 */
class PermitPdfService
{
    /** Data lembar SIK: pengajuan, penandatangan (akun "approver4"), dan tembusan alur. */
    public function data(PermitApplication $application): array
    {
        $application->load(['area', 'workType', 'items', 'personnel', 'approvalSteps.approver']);

        /* Nama General Manager selalu dibaca dari database agar perubahan nama akun
           langsung tercermin; view menampilkan garis kosong bila akun/nama tidak ada. */
        $signer = User::query()->where('username', 'approver4')->first();

        $tembusan = $application->approvalSteps
            ->sortBy('stage')
            ->map(fn ($step) => [
                'title' => trim((string) $step->approver_title),
                'name' => trim((string) $step->approver?->name),
            ])
            ->filter(fn (array $row) => $row['title'] !== '' || $row['name'] !== '')
            ->unique(fn (array $row) => $row['title'] . '|' . $row['name'])
            ->values();

        return [
            'application' => $application,
            'signer' => $signer,
            'tembusan' => $tembusan,
        ];
    }

    /** Nama berkas unduhan, mis. SIK-AYANI-2026-004.pdf */
    public function filename(PermitApplication $application): string
    {
        $number = (string) ($application->permit_number ?: $application->number);

        return str_replace(['/', '\\', ' '], '-', $number) . '.pdf';
    }

    /** Dokumen PDF siap unduh (DomPDF), memakai stylesheet & logo yang sama dengan pratinjau. */
    public function render(PermitApplication $application): \Barryvdh\DomPDF\PDF
    {
        $data = $this->data($application);
        $filename = $this->filename($application);

        /* Logo dokumen: InJourney Airports (bukan logo aplikasi SIK). Versi JPEG berlatar putih
           dipakai agar DomPDF tidak memerlukan ekstensi GD (PNG ber-alpha butuh GD); PNG hanya
           sebagai cadangan bila JPEG belum tersedia. */
        $logoPath = public_path('images/injourney-airports.jpg');
        if (! is_file($logoPath)) {
            $logoPath = public_path('images/injourney-airports.png');
        }
        $logoMime = str_ends_with(strtolower($logoPath), '.png') ? 'image/png' : 'image/jpeg';
        $data['logoSrc'] = is_file($logoPath)
            ? 'data:' . $logoMime . ';base64,' . base64_encode((string) file_get_contents($logoPath))
            : null;
        $data['sheetCss'] = (string) file_get_contents(public_path('css/sik-pdf.css'));
        $data['documentTitle'] = pathinfo($filename, PATHINFO_FILENAME);

        return Pdf::loadView('permits.pdf', $data)->setPaper('a4');
    }

    /** Isi PDF sebagai string biner (dipakai unduhan massal ZIP). */
    public function output(PermitApplication $application): string
    {
        return $this->render($application)->output();
    }
}
