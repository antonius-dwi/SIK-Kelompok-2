<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

/**
 * Halaman khusus daftar Peran. Peran adalah enum tetap (tanpa CRUD); halaman menampilkan peran yang dikenal sistem, jumlah akunnya, dan daftar penugasan akun per peran. Hapus penugasan menonaktifkan akun (riwayat tersimpan), bukan menghapus data, dan peran sistem tidak dapat dihapus.
 */
class RoleController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $counts = User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        $roles = collect(Role::cases())->map(fn (Role $role) => [
            'value' => $role->value,
            'label' => $role->label(),
            'home' => $role->homeRoute(),
            'count' => (int) ($counts[$role->value] ?? 0),
            'users' => User::with('departments')
                ->where('role', $role->value)
                ->orderBy('name')
                ->get(),
        ]);

        return view('system-admin.pages.roles', [
            'roles' => $roles,
            'totalUsers' => (int) $counts->sum(),
        ]);
    }

    /** Cabut penugasan akun terhadap perannya (menonaktifkan akun, bukan menghapus data). */
    public function revoke(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'Anda tidak dapat mencabut penugasan peran akun Anda sendiri.']);
        }

        if (! $user->is_active) {
            return back()->with('status', "Akun {$user->name} sudah nonaktif — penugasan peran {$user->role->label()} sudah tidak berlaku.");
        }

        if ($user->role === Role::SystemAdmin && $this->activeSystemAdmins() <= 1) {
            return back()->withErrors(['role' => 'Penugasan peran System Administrator terakhir tidak dapat dicabut — sistem akan kehilangan akses administrasi.']);
        }

        $user->update(['is_active' => false]);
        $this->audit->record(
            $request->user(),
            'Penugasan peran dicabut (akun dinonaktifkan)',
            $user->name . ' · ' . $user->role->label()
        );

        return back()->with('status', "Penugasan peran {$user->role->label()} untuk {$user->name} dicabut. Akun dinonaktifkan; riwayat SIK dan audit tetap tersimpan.");
    }

    private function activeSystemAdmins(): int
    {
        return User::where('role', Role::SystemAdmin->value)->where('is_active', true)->count();
    }
}
