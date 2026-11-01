<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationItem extends Model
{
    protected $fillable = [
        'permit_application_id', 'name', 'brand', 'model', 'serial_number', 'asset_number', 'quantity',
        'condition', 'movement', 'frequency', 'is_consumable', 'photo_path',
    ];

    protected function casts(): array
    {
        return ['is_consumable' => 'boolean', 'quantity' => 'integer'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }
}
