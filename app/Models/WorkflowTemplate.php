<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplate extends Model
{
    public const MAX_STEPS = 8;

    protected $fillable = ['area_id', 'work_type_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowTemplateStep::class)->orderBy('stage');
    }

    /** Templat aktif untuk kombinasi Area + Jenis pekerjaan (null bila tidak ada). */
    public static function resolve(?int $areaId, ?int $workTypeId): ?self
    {
        if (! $areaId || ! $workTypeId) {
            return null;
        }

        return static::query()->with('steps')
            ->where('is_active', true)
            ->where('area_id', $areaId)
            ->where('work_type_id', $workTypeId)
            ->first();
    }
}
