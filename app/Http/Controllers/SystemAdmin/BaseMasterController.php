<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CRUD ringan (tanpa hapus; nonaktifkan saja) untuk master Area / Jenis pekerjaan / Departemen.
 * Menyediakan juga halaman daftar per modul (index) yang dipakai navigasi sidebar
 * System Administrator — data, validasi, dan otorisasi tetap sama dengan operasi CRUD di atas.
 */
abstract class BaseMasterController extends Controller
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

    abstract protected function table(): string;

    abstract protected function label(): string;

    /** Judul halaman, mis. "Area kerja". */
    abstract protected function title(): string;

    /** Kata benda untuk tombol, mis. "area". */
    abstract protected function noun(): string;

    /** Judul kolom nama, mis. "NAMA AREA". */
    abstract protected function nameHead(): string;

    /** Slug route, mis. "areas" -> system-admin.areas.* */
    abstract protected function routeSlug(): string;

    public function __construct(protected readonly AuditLogger $audit) {}

    /** Halaman daftar khusus satu modul master data. */
    public function index(Request $request)
    {
        $rows = ($this->model())::orderBy('name')->get();

        return view('system-admin.pages.master', [
            'rows' => $rows,
            'title' => $this->title(),
            'noun' => $this->noun(),
            'nameHead' => $this->nameHead(),
            'routeSlug' => $this->routeSlug(),
            'activeCount' => $rows->where('is_active', true)->count(),
            'inactiveCount' => $rows->where('is_active', false)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $entry = ($this->model())::create($data + ['is_active' => true]);
        $this->audit->record($request->user(), "Data {$this->label()} ditambahkan", $entry->name);

        return back()->with('status', "{$this->label()} {$entry->name} ditambahkan.");
    }

    public function update(Request $request, int $id)
    {
        $entry = ($this->model())::findOrFail($id);
        $entry->update($this->validated($request, $entry));
        $this->audit->record($request->user(), "Data {$this->label()} diedit", $entry->name);

        return back()->with('status', "{$this->label()} {$entry->name} diperbarui.");
    }

    public function toggle(Request $request, int $id)
    {
        $entry = ($this->model())::findOrFail($id);
        $entry->update(['is_active' => ! $entry->is_active]);
        $this->audit->record($request->user(), "Master {$this->label()} " . ($entry->is_active ? 'diaktifkan' : 'dinonaktifkan'), $entry->name);

        return back()->with('status', "{$this->label()} {$entry->name} " . ($entry->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    /**
     * Hapus permanen master data.
     *
     * Hanya dijalankan bila data TIDAK direferensikan pengajuan/template/pengguna.
     * Bila masih dipakai, penghapusan ditolak dan pengguna diarahkan memakai
     * tombol nonaktifkan agar riwayat tetap utuh.
     */
    public function destroy(Request $request, int $id)
    {
        $entry = ($this->model())::findOrFail($id);
        $pemakaian = $this->references($entry);

        if ($pemakaian !== []) {
            $ringkas = collect($pemakaian)->map(fn ($n, $label) => "{$n} {$label}")->implode(', ');
            $this->audit->record($request->user(), 'Penghapusan ' . $this->label() . ' ditolak (masih dipakai)', $entry->name . ' · ' . $ringkas);

            return back()->withErrors([
                $this->routeSlug() => "Data {$this->label()} \"{$entry->name}\" tidak dapat dihapus karena masih dipakai pada {$ringkas}. Gunakan tombol nonaktifkan agar tidak dapat dipilih pada pengajuan baru tanpa menghilangkan riwayat.",
            ]);
        }

        $name = $entry->name;
        $entry->delete();
        $this->audit->record($request->user(), 'Data ' . $this->label() . ' dihapus', $name);

        return back()->with('status', "Data {$this->label()} {$name} dihapus.");
    }

    /**
     * Ringkasan referensi yang menghalangi penghapusan: ['pengajuan' => 3, ...].
     * Kosong = aman dihapus permanen. Diisi oleh tiap controller master data.
     *
     * @return array<string, int>
     */
    protected function references(Model $entry): array
    {
        return [];
    }

    protected function validated(Request $request, ?Model $entry = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique($this->table(), 'name')->ignore($entry?->id)],
            'code' => ['required', 'string', 'max:10', Rule::unique($this->table(), 'code')->ignore($entry?->id)],
        ]);
    }
}
