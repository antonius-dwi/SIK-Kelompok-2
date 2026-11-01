<?php

namespace App\Http\Controllers\SystemAdmin;

use Illuminate\Database\Eloquent\Model;

use App\Models\WorkflowTemplate;

use App\Models\PermitApplication;

use App\Models\WorkType;

class WorkTypeController extends BaseMasterController
{
    protected function model(): string
    {
        return WorkType::class;
    }

    protected function table(): string
    {
        return 'work_types';
    }

    protected function label(): string
    {
        return 'jenis pekerjaan';
    }

    protected function title(): string
    {
        return 'Jenis pekerjaan';
    }

    protected function noun(): string
    {
        return 'jenis pekerjaan';
    }

    protected function nameHead(): string
    {
        return 'NAMA JENIS PEKERJAAN';
    }

    protected function routeSlug(): string
    {
        return 'work-types';
    }

    /** Jenis pekerjaan dipakai pengajuan historis atau template alur. */
    protected function references(Model $entry): array
    {
        $pemakaian = [
            'pengajuan' => PermitApplication::where('work_type_id', $entry->id)->count(),
            'template alur' => WorkflowTemplate::where('work_type_id', $entry->id)->count(),
        ];

        return array_filter($pemakaian);
    }}
