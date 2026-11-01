<?php

namespace App\Http\Controllers\SystemAdmin;

use Illuminate\Database\Eloquent\Model;

use App\Models\WorkflowTemplate;

use App\Models\PermitApplication;

use App\Models\Area;

class AreaController extends BaseMasterController
{
    protected function model(): string
    {
        return Area::class;
    }

    protected function table(): string
    {
        return 'areas';
    }

    protected function label(): string
    {
        return 'area kerja';
    }

    protected function title(): string
    {
        return 'Area kerja';
    }

    protected function noun(): string
    {
        return 'area';
    }

    protected function nameHead(): string
    {
        return 'NAMA AREA';
    }

    protected function routeSlug(): string
    {
        return 'areas';
    }

    /** Area dipakai pengajuan historis atau template alur -> tidak boleh dihapus. */
    protected function references(Model $entry): array
    {
        $pemakaian = [
            'pengajuan' => PermitApplication::where('area_id', $entry->id)->count(),
            'template alur' => WorkflowTemplate::where('area_id', $entry->id)->count(),
        ];

        return array_filter($pemakaian);
    }}
