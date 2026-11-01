<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    public const REQUEST = 'request';
    public const ATTACHMENT = 'attachment';

    protected $fillable = ['permit_application_id', 'kind', 'original_name', 'path', 'mime', 'size'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }
}
