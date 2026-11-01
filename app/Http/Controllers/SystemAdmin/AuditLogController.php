<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Halaman khusus Log aktivitas (jejak audit). Hanya-baca: data diambil dari tabel audit_logs
 * yang sudah diisi AuditLogger; tidak ada operasi tulis di sini.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filter = (string) $request->query('action', '');

        $logs = AuditLog::query()
            ->when($filter !== '', fn ($query) => $query->where('action', 'like', "%{$filter}%"))
            ->latest('created_at')->latest('id')->limit(200)->get();

        return view('system-admin.pages.audit', [
            'logs' => $logs,
            'filter' => $filter,
            'total' => AuditLog::count(),
            'actions' => AuditLog::select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
