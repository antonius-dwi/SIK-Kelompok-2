<?php

namespace App\Http\Controllers\Approver;

use App\Http\Controllers\Controller;
use App\Models\PermitApplication;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApprovalController extends Controller
{
    public function __construct(private readonly ApplicationWorkflowService $workflow) {}

    /** Hanya pengajuan yang tahap berjalannya ditugaskan ke akun ini. */
    public function index(Request $request)
    {
        $applications = PermitApplication::awaitingApprover($request->user())
            ->with(['area', 'workType', 'approvalSteps'])->oldest('updated_at')->get();

        return view('approver.queue', ['applications' => $applications]);
    }

    public function show(PermitApplication $application)
    {
        $this->authorize('decide', $application);
        $application->load(['area', 'workType', 'personnel', 'items', 'documents', 'approvalSteps.approver', 'histories']);

        return view('approver.review', ['application' => $application]);
    }

    public function decide(Request $request, PermitApplication $application)
    {
        $this->authorize('decide', $application);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'return', 'reject'])],
            'comment' => [
                Rule::requiredIf(in_array($request->input('decision'), ['return', 'reject'], true)),
                'nullable', 'string', 'max:2000',
            ],
        ], ['comment.required' => 'Catatan wajib diisi untuk pengembalian atau penolakan.']);

        $application = $this->workflow->decide($application, $request->user(), $data['decision'], $data['comment'] ?? null);

        return redirect()->route('approver.queue')
            ->with('status', "Keputusan untuk {$application->number} tersimpan (status: {$application->status->label()}).");
    }
}
