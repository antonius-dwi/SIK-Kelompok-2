<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermitApplication;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;

/** Penerbitan SIK untuk pengajuan APPROVED. */
class IssuanceController extends Controller
{
    public function __construct(private readonly ApplicationWorkflowService $workflow) {}

    public function create(PermitApplication $application)
    {
        $this->authorize('issue', $application);
        $application->load(['area', 'workType']);

        return view('admin.issuance', [
            'application' => $application,
            'suggestedNumber' => $this->workflow->suggestPermitNumber(),
            'today' => now('Asia/Jakarta')->toDateString(),
        ]);
    }

    public function store(Request $request, PermitApplication $application)
    {
        $this->authorize('issue', $application);

        $data = $request->validate([
            // Format SIK/AYANI/TAHUN/NNN; keunikan dijamin juga oleh UNIQUE constraint di database.
            'permitNumber' => ['required', 'string', 'regex:#^SIK/AYANI/\d{4}/\d{3}$#', 'unique:permit_applications,permit_number'],
            'issueDate' => ['required', 'date'],
        ], [
            'permitNumber.regex' => 'Gunakan format SIK/AYANI/TAHUN/NNN, contoh SIK/AYANI/2026/001.',
            'permitNumber.unique' => 'Nomor SIK sudah digunakan.',
        ]);

        $this->workflow->issue($application, $request->user(), $data['permitNumber'], $data['issueDate']);

        return redirect()->route('admin.queues')
            ->with('status', "SIK {$data['permitNumber']} diterbitkan untuk {$application->number}.");
    }
}
