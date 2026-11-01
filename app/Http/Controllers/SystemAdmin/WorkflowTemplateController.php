<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Models\WorkType;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Templat alur persetujuan per kombinasi Area + Jenis pekerjaan.
 * Payload: area, workType, steps[i][title], steps[i][approver] (user id). Maksimal 8 tahap.
 */
class WorkflowTemplateController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Halaman khusus modul Templat alur kerja. */
    public function index()
    {
        $workflows = WorkflowTemplate::with(['area', 'workType', 'steps.approver'])->get()
            ->sortBy(fn ($t) => $t->area->name . $t->workType->name)->values();

        return view('system-admin.pages.workflows', [
            'workflows' => $workflows,
            'activeAreas' => Area::where('is_active', true)->orderBy('name')->get(),
            'activeTypes' => WorkType::where('is_active', true)->orderBy('name')->get(),
            'approvers' => User::activeApprovers()->orderBy('name')->get(),
            'activeCount' => $workflows->where('is_active', true)->count(),
            'inactiveCount' => $workflows->where('is_active', false)->count(),
        ]);
    }

    /** Halaman khusus daftar Langkah persetujuan (gabungan seluruh templat alur). */
    public function steps()
    {
        $workflows = WorkflowTemplate::with(['area', 'workType', 'steps.approver'])->get()
            ->sortBy(fn ($t) => $t->area->name . $t->workType->name)->values();

        $steps = $workflows->flatMap(fn ($flow) => $flow->steps->map(fn ($step) => [
            'flow' => $flow,
            'stage' => $step->stage,
            'title' => $step->approver_title,
            'approver' => $step->approver,
        ]));

        return view('system-admin.pages.steps', [
            'steps' => $steps,
            'totalSteps' => $steps->count(),
            'usedTemplates' => $workflows->filter(fn ($flow) => $flow->steps->isNotEmpty())->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $template = DB::transaction(function () use ($data) {
            $template = WorkflowTemplate::create([
                'area_id' => $data['area'], 'work_type_id' => $data['workType'], 'is_active' => true,
            ]);
            $this->syncSteps($template, $data['steps']);

            return $template;
        });

        $this->audit->record($request->user(), 'Data workflow ditambahkan', $this->title($template));

        return back()->with('status', 'Templat alur ditambahkan.');
    }

    public function update(Request $request, WorkflowTemplate $workflow)
    {
        $data = $this->validated($request, $workflow);

        DB::transaction(function () use ($workflow, $data) {
            $workflow->update(['area_id' => $data['area'], 'work_type_id' => $data['workType']]);
            $this->syncSteps($workflow, $data['steps']);
        });

        $this->audit->record($request->user(), 'Data workflow diedit', $this->title($workflow));

        return back()->with('status', 'Templat alur diperbarui. Pengajuan yang sudah berjalan tetap memakai alur saat diverifikasi.');
    }

    public function toggle(Request $request, WorkflowTemplate $workflow)
    {
        $workflow->update(['is_active' => ! $workflow->is_active]);
        $this->audit->record($request->user(), 'Master workflow ' . ($workflow->is_active ? 'diaktifkan' : 'dinonaktifkan'), $this->title($workflow));

        return back()->with('status', 'Status templat alur diubah.');
    }

    private function validated(Request $request, ?WorkflowTemplate $current = null): array
    {
        return $request->validate([
            'area' => ['required', Rule::exists('areas', 'id')->where('is_active', true)],
            'workType' => [
                'required', Rule::exists('work_types', 'id')->where('is_active', true),
                // Kombinasi Area + Jenis harus unik.
                Rule::unique('workflow_templates', 'work_type_id')
                    ->where('area_id', $request->input('area'))->ignore($current?->id),
            ],
            'steps' => ['required', 'array', 'min:1', 'max:' . WorkflowTemplate::MAX_STEPS],
            'steps.*.title' => ['required', 'string', 'max:255'],
            'steps.*.approver' => [
                'required',
                Rule::exists('users', 'id')->where('role', Role::Approver->value)->where('is_active', true),
            ],
        ], [
            'workType.unique' => 'Kombinasi area dan jenis pekerjaan sudah memiliki templat.',
            'steps.*.approver.exists' => 'Pilih akun Approver yang aktif pada setiap tahap.',
        ]);
    }

    private function syncSteps(WorkflowTemplate $template, array $steps): void
    {
        $template->steps()->delete();
        foreach (array_values($steps) as $i => $step) {
            $template->steps()->create([
                'stage' => $i + 1,
                'approver_title' => trim($step['title']),
                'approver_user_id' => $step['approver'],
            ]);
        }
    }

    private function title(WorkflowTemplate $template): string
    {
        $template->loadMissing(['area', 'workType']);

        return "{$template->area->name} + {$template->workType->name}";
    }
}
