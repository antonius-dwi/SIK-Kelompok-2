<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus as S;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PermitApplication;
use Illuminate\Http\Request;

/** Antrean Admin: verifikasi, koreksi, penerbitan, plus riwayat & audit per pengajuan. */
class QueueController extends Controller
{
    public function index(Request $request)
    {
        $with = ['area', 'workType', 'owner'];

        $submitted = PermitApplication::withStatus(S::Submitted, S::Verification)->with($with)->oldest('submitted_at')->get();
        $corrections = PermitApplication::withStatus(S::Correction)->with($with)->latest('updated_at')->get();
        $approved = PermitApplication::withStatus(S::Approved)->with($with)->latest('updated_at')->get();

        $selected = $request->filled('application')
            ? PermitApplication::with('histories')->find($request->query('application'))
            : null;

        return view('admin.queues', [
            'submitted' => $submitted,
            'corrections' => $corrections,
            'approved' => $approved,
            'reviewCount' => $submitted->count() + $corrections->count() + $approved->count(),
            'applicationOptions' => PermitApplication::orderByDesc('id')->get(['id', 'number', 'applicant_name']),
            'selected' => $selected,
            'selectedAudit' => $selected
                ? AuditLog::where('target', $selected->number)->latest('created_at')->latest('id')->get()
                : collect(),
        ]);
    }
}
