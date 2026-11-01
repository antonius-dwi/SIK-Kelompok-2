<?php

namespace App\Http\Controllers\Applicant;

use App\Enums\ApplicationStatus as S;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveApplicationRequest;
use App\Models\Area;
use App\Models\PermitApplication;
use App\Models\User;
use App\Models\WorkType;
use App\Models\WorkflowTemplate;
use App\Services\ApplicationWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationWorkflowService $workflow) {}

    /** Beranda pemohon: ringkasan, perlu tindakan, dan daftar "Pengajuan saya". */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $filter = $request->query('status', 'all');

        $own = PermitApplication::ownedBy($user);
        $counts = (clone $own)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $applications = (clone $own)->with(['area', 'workType'])
            ->when(S::tryFrom((string) $filter), fn ($q, $status) => $q->where('status', $status->value))
            ->latest('updated_at')->paginate(15)->withQueryString();

        return view('applicant.dashboard', [
            'applications' => $applications,
            'filter' => $filter,
            'statuses' => S::cases(),
            'statusCounts' => $counts,
            'totalCount' => $counts->sum(),
            'inProgressCount' => collect(S::inProgress())->sum(fn ($s) => (int) ($counts[$s->value] ?? 0)),
            'approvedCount' => (int) ($counts[S::Approved->value] ?? 0) + (int) ($counts[S::Issued->value] ?? 0),
            'itemCount' => DB::table('application_items')
                ->join('permit_applications', 'permit_applications.id', '=', 'application_items.permit_application_id')
                ->where('permit_applications.user_id', $user->id)->count(),
            'needsAction' => (clone $own)->withStatus(S::Revision, S::Draft)->latest('updated_at')->get(),
        ]);
    }

    public function create()
    {
        return view('applicant.applications.form', $this->formData(null));
    }

    public function store(SaveApplicationRequest $request)
    {
        $application = DB::transaction(function () use ($request) {
            $application = $this->workflow->createDraft($request->user());

            return $request->isSubmit()
                ? $this->workflow->submit($application, $request->user(), $request)
                : $this->workflow->saveDraft($application, $request->user(), $request);
        });

        return $this->afterSave($request, $application);
    }

    public function show(PermitApplication $application)
    {
        $this->authorize('view', $application);
        $application->load(['area', 'workType', 'approvalSteps.approver', 'histories', 'personnel', 'items', 'documents']);

        return view('applicant.applications.show', ['application' => $application]);
    }

    public function edit(PermitApplication $application)
    {
        $this->authorize('update', $application);

        return view('applicant.applications.form', $this->formData($application));
    }

    public function update(SaveApplicationRequest $request, PermitApplication $application)
    {
        $this->authorize('update', $application);

        $application = $request->isSubmit()
            ? $this->workflow->submit($application, $request->user(), $request)
            : $this->workflow->saveDraft($application, $request->user(), $request);

        return $this->afterSave($request, $application);
    }

    /** Lembar SIK yang dapat dicetak / disimpan sebagai PDF (hanya status ISSUED). */
    public function permit(Request $request, PermitApplication $application)
    {
        $this->authorize('printPermit', $application);
        $this->applyDraftPreview($request, $application);
        $data = $this->permitData($application);

        /* ?embed=1 → tampilan tanpa kerangka aplikasi, dipakai iframe di modal pratinjau.
           Keduanya melewati otorisasi yang sama (authorize printPermit di atas). */
        if ($request->boolean('embed')) {
            return view('permits.embed', $data);
        }

        return view('permits.print', $data);
    }

    /** Unduh SIK sebagai berkas PDF (DomPDF) — otorisasi sama dengan halaman cetak. */
    public function permitPdf(Request $request, PermitApplication $application)
    {
        $this->authorize('printPermit', $application);
        $this->applyDraftPreview($request, $application);

        /* Render PDF lewat PermitPdfService supaya jalur pemohon, admin, dan Rekap SIK
           memakai template, logo, dan stylesheet yang sama persis. */
        $pdf = app(\App\Services\PermitPdfService::class);

        return $pdf->render($application)->download($pdf->filename($application));
    }

    /** Data lembar SIK (delegasi ke PermitPdfService — sumber tunggal). */
    private function permitData(PermitApplication $application): array
    {
        return app(\App\Services\PermitPdfService::class)->data($application);
    }

    /**
     * Pratinjau SIK sebelum diterbitkan (halaman admin "Terbitkan SIK").
     *
     * Nomor & tanggal dari formulir penerbitan dipakai hanya untuk tampilan dokumen
     * (in-memory, tidak disimpan), sehingga tidak ada perubahan data maupun audit log.
     * Setelah SIK terbit, data asli dari database yang dipakai.
     */
    private function applyDraftPreview(Request $request, PermitApplication $application): void
    {
        if ($application->status === S::Issued) {
            return;
        }

        // Hanya admin yang berwenang menerbitkan pengajuan ini yang boleh memakai isian ini.
        $this->authorize('issue', $application);

        $data = $request->validate([
            'number' => ['nullable', 'string', 'regex:#^SIK/AYANI/\d{4}/\d{3}$#'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if (filled($data['number'] ?? null)) {
            $application->permit_number = $data['number'];
        }

        if (filled($data['date'] ?? null)) {
            $application->issue_date = $data['date'];
        }
    }

    /** Nama berkas aman dari nomor SIK di database (mis. SIK-AYANI-2026-001.pdf). */
    private function permitFilename(PermitApplication $application): string
    {
        $base = (string) ($application->permit_number ?: $application->number);
        $safe = trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $base), '-');

        return ($safe !== '' ? $safe : 'SIK') . '.pdf';
    }

    private function afterSave(SaveApplicationRequest $request, PermitApplication $application)
    {
        if ($request->isSubmit()) {
            return redirect()->route('applicant.dashboard')
                ->with('status', "{$application->number} berhasil dikirim.");
        }

        return redirect()->route('applicant.applications.edit', $application)
            ->with('status', "{$application->number} tersimpan sebagai draf.");
    }

    private function formData(?PermitApplication $application): array
    {
        $application?->load(['personnel', 'items', 'documents']);

        return [
            'application' => $application,
            'areas' => Area::active()->orderBy('name')->get(),
            'workTypes' => WorkType::active()->orderBy('name')->get(),
            // Kombinasi Area + Jenis pekerjaan yang punya templat alur aktif
            // dengan minimal satu tahap; dipakai peringatan di form pemohon saja.
            'templateCombos' => WorkflowTemplate::query()->where('is_active', true)->whereHas('steps')
                ->get(['area_id', 'work_type_id'])
                ->map(fn ($t) => $t->area_id . ':' . $t->work_type_id)
                ->values()->all(),
        ];
    }
}
