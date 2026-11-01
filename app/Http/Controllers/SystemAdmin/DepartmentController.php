<?php

namespace App\Http\Controllers\SystemAdmin;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

use App\Models\Department;

class DepartmentController extends BaseMasterController
{
    protected function model(): string
    {
        return Department::class;
    }

    protected function table(): string
    {
        return 'departments';
    }

    protected function label(): string
    {
        return 'departemen';
    }

    protected function title(): string
    {
        return 'Departemen';
    }

    protected function noun(): string
    {
        return 'departemen';
    }

    protected function nameHead(): string
    {
        return 'NAMA DEPARTEMEN';
    }

    protected function routeSlug(): string
    {
        return 'departments';
    }

    /** Departemen dipakai akun (unit utama atau penugasan multi-unit) -> jangan dihapus. */
    protected function references(Model $entry): array
    {
        $pengguna = User::where('department_id', $entry->id)->count();
        $penugasan = User::whereHas('departments', fn ($q) => $q->where('departments.id', $entry->id))->count();

        return array_filter([
            'pengguna' => max($pengguna, $penugasan),
        ]);
    }}
