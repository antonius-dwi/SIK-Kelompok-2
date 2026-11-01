<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'permit_application_id', 'status', 'stage', 'actor_id', 'actor_name', 'actor_role', 'decision', 'comment',
    ];

    protected function casts(): array
    {
        return ['status' => ApplicationStatus::class, 'created_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }
}
