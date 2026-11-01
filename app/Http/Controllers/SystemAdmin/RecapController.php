<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\PermitApplication;
use App\Services\PermitPdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Rekap SIK untuk administrator.
 *
 * Filter periode memakai dua basis tanggal yang berbeda dan tidak dicampur:
 * - "terbit"   : issue_date (dokumen benar-benar terbit, wajib bernomor SIK),
 * - "diajukan" : submitted_at (kapan pemohon mengirim pengajuan).
 */
class RecapController extends Controller
{
    public function __construct(private readonly PermitPdfService $pdf) {}

    public function index(Request $request)
    {
        $this->pastikanAdministrator($request);
        $filters = $this->filters($request);
        $rows = $this->query($filters)->paginate(25)->withQueryString();

        return view('system-admin.pages.recap', [
            'rows' => $rows,
            'filters' => $filters,
            'totalTerbit' => (clone $this->query($filters))->whereNotNull('permit_number')->count(),
            'totalDiajukan' => $this->query([...$filters, 'basis' => 'diajukan'])->count(),
        ]);
    }

    /** Unduh satu dokumen SIK (hanya jika benar-benar sudah terbit). */
    public function downloadPdf(Request $request, PermitApplication $application)
    {
        $this->pastikanAdministrator($request);

        abort_unless(
            $application->status === ApplicationStatus::Issued && filled($application->permit_number),
            404,
            'Dokumen SIK belum diterbitkan.'
        );

        return $this->pdf->render($application)->download($this->pdf->filename($application));
    }

    /** Unduh massal dokumen SIK pada periode terpilih sebagai ZIP. */
    public function downloadZip(Request $request): BinaryFileResponse|RedirectResponse
    {
        $this->pastikanAdministrator($request);
        $filters = $this->filters($request);

        $rows = $this->query($filters)
            ->where('status', ApplicationStatus::Issued->value)
            ->whereNotNull('permit_number')
            ->get();

        if ($rows->isEmpty()) {
            return redirect()
                ->route('system-admin.recap.index', $filters)
                ->withErrors([
                    'rekap' => 'Tidak ada SIK terbit pada periode ' . $filters['from'] . ' s.d. ' . $filters['to'] . ' sehingga tidak ada berkas untuk diunduh.',
                ]);
        }

        $dir = storage_path('app/private/rekap');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $zipPath = tempnam($dir, 'rekap-sik-');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::OVERWRITE);

        $dipakai = [];
        foreach ($rows as $row) {
            $nama = $this->pdf->filename($row);
            if (isset($dipakai[$nama])) {           // hindari entri ganda
                $nama = pathinfo($nama, PATHINFO_FILENAME) . '-' . $row->id . '.pdf';
            }
            $dipakai[$nama] = true;
            $zip->addFromString($nama, $this->pdf->output($row));
        }
        $zip->close();

        $label = $filters['from'] . '_sd_' . $filters['to'] . '_' . $filters['basis'];
        $namaZip = 'rekap-sik-' . str_replace(['/', ' '], '-', $label) . '.zip';

        return response()->download($zipPath, $namaZip, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /** @return array{from: string, to: string, basis: string} */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'basis' => ['nullable', Rule::in(['terbit', 'diajukan'])],
        ], [
            'to.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
            'from.date' => 'Tanggal mulai tidak valid.',
            'to.date' => 'Tanggal akhir tidak valid.',
        ]);

        return [
            'from' => $data['from'] ?? now()->startOfMonth()->toDateString(),
            'to' => $data['to'] ?? now()->toDateString(),
            'basis' => $data['basis'] ?? 'terbit',
        ];
    }

    private function query(array $filters): Builder
    {
        $query = PermitApplication::query()->with(['area', 'workType']);

        if ($filters['basis'] === 'diajukan') {
            return $query
                ->whereNotNull('submitted_at')
                ->whereDate('submitted_at', '>=', $filters['from'])
                ->whereDate('submitted_at', '<=', $filters['to'])
                ->orderByDesc('submitted_at')
                ->orderByDesc('id');
        }

        return $query
            ->whereNotNull('permit_number')
            ->where('status', ApplicationStatus::Issued->value)
            ->whereDate('issue_date', '>=', $filters['from'])
            ->whereDate('issue_date', '<=', $filters['to'])
            ->orderByDesc('issue_date')
            ->orderByDesc('id');
    }

    /** Kontrol server-side: menu ini hanya untuk System Administrator. */
    private function pastikanAdministrator(Request $request): void
    {
        abort_unless($request->user()?->role === Role::SystemAdmin, 403);
    }
}
