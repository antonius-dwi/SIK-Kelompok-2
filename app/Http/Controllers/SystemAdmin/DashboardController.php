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

/**
 * Dashboard System administrator: ringkasan konfigurasi, distribusi pengguna,
 * aktivitas sistem terakhir, dan pintasan ke halaman pengelolaan master data.
 *
 * Halaman pengelolaan lama (system-admin.index) tidak diubah dan tetap tersedia.
 * Semua angka diambil dari database; tidak ada angka hardcode dari mockup.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeUsers = User::where('is_active', true)->count();
        $activeWorkflows = WorkflowTemplate::where('is_active', true)->count();
        $activeAreas = Area::where('is_active', true)->count();
        $activeWorkTypes = WorkType::where('is_active', true)->count();

        $totalUsers = User::count();
        $totalWorkflows = WorkflowTemplate::count();
        $totalAreas = Area::count();
        $totalWorkTypes = WorkType::count();

        // Penanda tren pada kartu KPI: persentase data yang dibuat dalam 30 hari terakhir.
        // Dihitung dari kolom created_at (data nyata), bukan angka contoh dari mockup.
        $trend30 = function (string $model): int {
            $total = $model::count();
            if ($total === 0) {
                return 0;
            }

            return (int) round($model::where('created_at', '>=', now()->subDays(30))->count() / $total * 100);
        };

        // Distribusi pengguna per peran: label & jumlah asli dari tabel users.
        $roleDistribution = collect(Role::cases())
            ->map(fn (Role $role) => [
                'label' => $role->label(),
                'count' => User::where('role', $role->value)->count(),
            ])
            ->values();
        $maxRoleCount = max(1, $roleDistribution->max('count'));

        // Ringkasan konfigurasi: total/aktif/nonaktif dari kolom is_active.
        // Peran tidak punya status aktif/nonaktif (enum tetap) -> null dan
        // ditampilkan sebagai "—" di view, bukan nilai yang ditembak.
        $configSummary = [
            ['label' => 'Area kerja', 'total' => $totalAreas, 'active' => $activeAreas, 'inactive' => Area::where('is_active', false)->count(), 'target' => 'areas'],
            ['label' => 'Jenis pekerjaan', 'total' => $totalWorkTypes, 'active' => $activeWorkTypes, 'inactive' => WorkType::where('is_active', false)->count(), 'target' => 'work-types'],
            ['label' => 'Departemen', 'total' => Department::count(), 'active' => Department::where('is_active', true)->count(), 'inactive' => Department::where('is_active', false)->count(), 'target' => 'departments'],
            ['label' => 'Template alur kerja', 'total' => $totalWorkflows, 'active' => $activeWorkflows, 'inactive' => WorkflowTemplate::where('is_active', false)->count(), 'target' => 'workflows'],
            ['label' => 'Pengguna', 'total' => $totalUsers, 'active' => $activeUsers, 'inactive' => User::where('is_active', false)->count(), 'target' => 'users'],
            ['label' => 'Peran', 'total' => count(Role::cases()), 'active' => null, 'inactive' => null, 'target' => 'roles'],
        ];

        return view('system-admin.dashboard', [
            'summaryCards' => [
                ['label' => 'Pengguna aktif', 'count' => $activeUsers, 'note' => "dari {$totalUsers} total pengguna", 'icon' => 'fa-user', 'trend' => $trend30(User::class)],
                ['label' => 'Template alur aktif', 'count' => $activeWorkflows, 'note' => "dari {$totalWorkflows} total template", 'icon' => 'fa-layer-group', 'trend' => $trend30(WorkflowTemplate::class)],
                ['label' => 'Area kerja aktif', 'count' => $activeAreas, 'note' => "dari {$totalAreas} total area", 'icon' => 'fa-database', 'trend' => $trend30(Area::class)],
                ['label' => 'Jenis pekerjaan aktif', 'count' => $activeWorkTypes, 'note' => "dari {$totalWorkTypes} total jenis pekerjaan", 'icon' => 'fa-briefcase', 'trend' => $trend30(WorkType::class)],
            ],
            'roleDistribution' => $roleDistribution,
            'maxRoleCount' => $maxRoleCount,
            'configSummary' => $configSummary,
            'recentActivity' => AuditLog::latest('created_at')->latest('id')->limit(8)->get(),
            'recentApplications' => PermitApplication::with(['area', 'workType'])
                ->orderByDesc('id')->limit(6)->get(),
        ]);
    }
}
