<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\ApplicationApprovalStep;
use App\Models\ApplicationHistory;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\PermitApplication;
use App\Models\User;
use App\Models\WorkflowTemplateStep;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Halaman khusus modul Pengguna (navigasi sidebar). */
    public function index(Request $request)
    {
        $allUsers = User::with(['department', 'departments'])->orderBy('name')->get();

        // Filter peran via ?role=...; nilai di luar enum diabaikan (kembali ke Semua).
        $roleFilter = $request->query('role', '');
        if (! in_array($roleFilter, array_map(fn (Role $role) => $role->value, Role::cases()), true)) {
            $roleFilter = '';
        }
        $selectedRole = $roleFilter === '' ? null : Role::from($roleFilter);
        $users = $selectedRole === null
            ? $allUsers
            : $allUsers->filter(fn (User $user) => $user->role === $selectedRole)->values();

        return view('system-admin.pages.users', [
            'users' => $users,
            'roles' => Role::cases(),
            'activeDepartments' => Department::where('is_active', true)->orderBy('name')->get(),
            'activeCount' => $allUsers->where('is_active', true)->count(),
            'inactiveCount' => $allUsers->where('is_active', false)->count(),
            'totalUsers' => $allUsers->count(),
            'roleFilter' => $roleFilter,
            'selectedRole' => $selectedRole,
            'roleCounts' => $allUsers->groupBy(fn (User $user) => $user->role->value)->map->count()->all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'password' => ['required', Password::min(8)],
            'role' => ['required', Rule::enum(Role::class)],
            // Penugasan unit: boleh lebih dari satu (khususnya Approver).
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => ['integer', 'distinct', Rule::exists('departments', 'id')->where('is_active', true)],
        ], [
            'departments.required' => 'Pilih minimal satu unit/departemen.',
        ]);

        $units = $this->unitIds($request, $data);
        unset($data['departments']);

        $user = User::create($data + ['is_active' => true]); // password di-hash oleh cast `hashed`
        $user->syncDepartments($units);
        $this->audit->record($request->user(), 'Data user ditambahkan', $user->name . ' · unit: ' . $this->unitNames($user));

        return back()->with('status', "Pengguna {$user->name} ditambahkan.");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', Password::min(8)], // kosong = pertahankan password lama
            'role' => ['required', Rule::enum(Role::class)],
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => ['integer', 'distinct', 'exists:departments,id'],
        ], [
            'departments.required' => 'Pilih minimal satu unit/departemen.',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }
        // Cegah System admin mencabut perannya sendiri (bisa mengunci sistem).
        if ($user->is($request->user()) && $data['role'] !== Role::SystemAdmin->value) {
            return back()->withErrors(['role' => 'Anda tidak dapat mengubah peran akun Anda sendiri.']);
        }

        $units = $this->unitIds($request, $data);
        unset($data['departments']);

        $user->update($data);
        $user->syncDepartments($units);
        $this->audit->record($request->user(), 'Data user diedit', $user->name . ' · unit: ' . $this->unitNames($user));

        return back()->with('status', "Pengguna {$user->name} diperbarui.");
    }

    public function toggle(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        $user->update(['is_active' => ! $user->is_active]);
        $this->audit->record($request->user(), 'Master user ' . ($user->is_active ? 'diaktifkan' : 'dinonaktifkan'), $user->name);

        return back()->with('status', "Pengguna {$user->name} " . ($user->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    /** Hapus pengguna. Akun yang masih direferensikan data historis tidak dihapus permanen, hanya dinonaktifkan agar integritas data tetap utuh; akun tanpa riwayat apa pun boleh dihapus permanen. */
    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($this->isLastActiveSystemAdmin($user)) {
            return back()->withErrors(['user' => 'Akun System Administrator terakhir tidak dapat dihapus — sistem akan kehilangan akses administrasi.']);
        }

        $alasan = $this->historicalReferences($user);

        if ($alasan !== []) {
            $user->update(['is_active' => false]);
            $this->audit->record($request->user(), 'Penghapusan user ditolak (ada riwayat) — akun dinonaktifkan', $user->name . ' · ' . implode(', ', $alasan));

            return back()->with('status', "Pengguna {$user->name} tidak dihapus permanen karena masih terhubung ke " . implode(', ', $alasan) . '. Akun dinonaktifkan dan riwayat tetap tersimpan.');
        }

        $name = $user->name;
        $user->departments()->detach();
        $user->delete();
        $this->audit->record($request->user(), 'Data user dihapus', $name);

        return back()->with('status', "Pengguna {$name} dihapus (tidak memiliki data historis).");
    }

    /** Daftar unit terpilih; mendukung kiriman lama berbentuk department_id tunggal. */
    private function unitIds(Request $request, array $data): array
    {
        $units = $request->input('departments');
        if (! is_array($units) || $units === []) {
            $units = [$data['department_id'] ?? $request->input('department_id')];
        }

        return array_values(array_filter(array_map('intval', $units)));
    }

    private function unitNames(User $user): string
    {
        return $user->departments()->orderBy('name')->pluck('name')->implode(', ') ?: '—';
    }

    private function isLastActiveSystemAdmin(User $user): bool
    {
        if ($user->role !== Role::SystemAdmin) {
            return false;
        }

        return User::where('role', Role::SystemAdmin->value)
            ->where('is_active', true)
            ->whereKeyNot($user->getKey())
            ->count() === 0;
    }

    /** @return list<string> ringkasan relasi historis; kosong = aman dihapus permanen */
    public function historicalReferences(User $user): array
    {
        $cek = [
            'pengajuan' => PermitApplication::where('user_id', $user->id)->count(),
            'langkah persetujuan' => ApplicationApprovalStep::where('approver_user_id', $user->id)->count(),
            'riwayat pengajuan' => ApplicationHistory::where('actor_id', $user->id)->count(),
            'log audit' => AuditLog::where('user_id', $user->id)->count(),
            'langkah template alur' => WorkflowTemplateStep::where('approver_user_id', $user->id)->count(),
        ];

        $alasan = [];
        foreach ($cek as $label => $jumlah) {
            if ($jumlah > 0) {
                $alasan[] = "{$jumlah} {$label}";
            }
        }

        return $alasan;
    }
}
