<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermitApplication;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VerificationController extends Controller
{
    public function __construct(private readonly ApplicationWorkflowService $workflow) {}

    /** SUBMITTED -> VERIFICATION, lalu buka halaman/modal pemeriksaan. */
    public function start(Request $request, PermitApplication $application)
    {
        $this->authorize('verify', $application);
        $this->workflow->startVerification($application, $request->user());

        return redirect()->route('admin.verification.show', $application);
    }

    public function show(PermitApplication $application)
    {
        $this->authorize('verify', $application);
        $application->load(['area', 'workType', 'personnel', 'items', 'documents']);

        return view('admin.verification', ['application' => $application]);
    }

    /** action=forward (teruskan ke persetujuan) atau action=revision (kembalikan ke pemohon, catatan wajib). */
    public function process(Request $request, PermitApplication $application)
    {
        $this->authorize('verify', $application);

        $data = $request->validate([
            'verificationAction' => ['required', Rule::in(['forward', 'revision'])],
            'verificationNote' => [
                Rule::requiredIf($request->input('verificationAction') === 'revision'),
                'nullable', 'string', 'max:2000',
            ],
        ], ['verificationNote.required' => 'Catatan wajib diisi untuk revisi.']);

        $note = trim((string) ($data['verificationNote'] ?? '')) ?: null;

        if ($data['verificationAction'] === 'revision') {
            $this->workflow->returnForRevision($application, $request->user(), $note);
            $message = "{$application->number} dikembalikan ke pemohon (REVISION).";
        } else {
            $this->workflow->forwardToApproval($application, $request->user(), $note);
            $message = "{$application->number} diteruskan ke alur persetujuan.";
        }

        return redirect()->route('admin.queues')->with('status', $message);
    }
}
