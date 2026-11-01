<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationPersonnel extends Model
{
    protected $table = 'application_personnel';

    protected $fillable = ['permit_application_id', 'name', 'phone_number', 'vehicle_plate'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }
}
