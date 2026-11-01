<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\PermitApplication;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Models\WorkType;
use Illuminate\Http\Request;

/** Halaman tunggal "Master data & konfigurasi" untuk System administrator. */
class MasterDataController extends Controller
{
    public function index(Request $request)
    {

        $selected = $request->filled('application')
        ? PermitApplication::with('histories')->find($request->query('application'))
        : null;

        return view('system-admin.index', [
            'selected' => $selected,
            'areas' => Area::orderBy('name')->get(),
            'workTypes' => WorkType::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'workflows' => WorkflowTemplate::with(['area', 'workType', 'steps.approver'])->get()
                ->sortBy(fn ($t) => $t->area->name . $t->workType->name)->values(),
            'users' => User::with('department')->orderBy('name')->get(),
            'roles' => Role::cases(),
            'approvers' => User::activeApprovers()->orderBy('name')->get(),
            'applicationOptions' => PermitApplication::orderByDesc('id')->get(['id', 'number', 'applicant_name']),
            'auditLogs' => AuditLog::latest('created_at')->latest('id')->limit(200)->get(),
        ]);
    }
}