<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CorrectApplicationRequest;
use App\Models\Area;
use App\Models\PermitApplication;
use App\Models\WorkType;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;

/** Koreksi oleh Admin atas pengajuan yang di-RETURN approver. */
class CorrectionController extends Controller
{
    public function __construct(private readonly ApplicationWorkflowService $workflow) {}

    public function edit(PermitApplication $application)
    {
        $this->authorize('correct', $application);
        $application->load(['area', 'workType', 'personnel', 'items', 'documents', 'histories']);

        return view('admin.correction', [
            'application' => $application,
            'areas' => Area::active()->orderBy('name')->get(),
            'workTypes' => WorkType::active()->orderBy('name')->get(),
            // Catatan RETURN terakhir dari approver.
            'returnEvent' => $application->histories->reverse()->firstWhere('decision', 'RETURN'),
        ]);
    }

    public function update(CorrectApplicationRequest $request, PermitApplication $application)
    {
        $this->authorize('correct', $application);

        $application = $this->workflow->applyCorrection(
            $application, $request->user(), $request, trim($request->input('correctionNote'))
        );

        return redirect()->route('admin.queues')
            ->with('status', "{$application->number} dikirim kembali ke approver tahap {$application->current_stage}.");
    }

    /** Surat permohonan tidak valid: tutup pengajuan (CLOSED). */
    public function close(Request $request, PermitApplication $application)
    {
        $this->authorize('correct', $application);

        $request->validate([
            'invalidRequestLetter' => ['accepted'],
            'correctionNote' => ['required', 'string', 'max:2000'],
        ], [
            'invalidRequestLetter.accepted' => 'Tandai surat permohonan tidak valid terlebih dahulu untuk menutup pengajuan.',
            'correctionNote.required' => 'Alasan surat tidak valid wajib diisi.',
        ]);

        $this->workflow->closeInvalid($application, $request->user(), trim($request->input('correctionNote')));

        return redirect()->route('admin.queues')
            ->with('status', "{$application->number} ditutup. Pemohon perlu membuat pengajuan baru dari awal.");
    }
}
