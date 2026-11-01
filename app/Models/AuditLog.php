<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'user_name', 'role', 'action', 'target', 'detail'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
