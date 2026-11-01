<?php

namespace App\Http\Controllers\Management;

use App\Enums\ApplicationStatus as S;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\PermitApplication;
use App\Models\WorkType;
use Illuminate\Http\Request;

/** Pemantauan hanya-baca untuk Management. */
class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'area' => ['nullable', 'integer'],
            'workType' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:' . implode(',', array_column(S::cases(), 'value'))],
            'dateFrom' => ['nullable', 'date'],
            'dateTo' => ['nullable', 'date', 'after_or_equal:dateFrom'],
        ]);

        // Tanggal pengajuan = tanggal dibuat.
        $base = PermitApplication::query()
            ->when($filters['area'] ?? null, fn ($q, $v) => $q->where('area_id', $v))
            ->when($filters['workType'] ?? null, fn ($q, $v) => $q->where('work_type_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['dateFrom'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['dateTo'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        $applications = (clone $base)->with(['area', 'workType', 'items'])->latest()->paginate(20)->withQueryString();

        // Volume 6 bulan terakhir (termasuk bulan berjalan) dari data terfilter.
        $start = now('Asia/Jakarta')->startOfMonth()->subMonths(5);
        $perMonth = (clone $base)->where('created_at', '>=', $start)->pluck('created_at')
            ->countBy(fn ($d) => $d->copy()->timezone('Asia/Jakarta')->format('Y-m'));
        $monthly = collect(range(0, 5))->map(function ($i) use ($start, $perMonth) {
            $month = $start->copy()->addMonths($i);

            return ['label' => $month->translatedFormat('M'), 'count' => (int) ($perMonth[$month->format('Y-m')] ?? 0)];
        });

        $byStatus = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $byStatus->sum();
        $distribution = collect(S::cases())->map(fn (S $s) => [
            'status' => $s, 'count' => (int) ($byStatus[$s->value] ?? 0),
            'percent' => $total ? round(($byStatus[$s->value] ?? 0) / $total * 100, 1) : 0,
        ]);

        return view('management.index', [
            'applications' => $applications,
            'monthly' => $monthly,
            'distribution' => $distribution,
            'filters' => $filters,
            'areas' => Area::orderBy('name')->get(),
            'workTypes' => WorkType::orderBy('name')->get(),
            'statuses' => S::cases(),
        ]);
    }

    public function show(PermitApplication $application)
    {
        $this->authorize('view', $application);
        $application->load(['area', 'workType', 'personnel', 'items', 'documents', 'histories', 'approvalSteps.approver']);

        return view('management.show', ['application' => $application]);
    }
}
