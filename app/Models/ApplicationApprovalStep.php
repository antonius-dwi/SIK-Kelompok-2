<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationApprovalStep extends Model
{
    protected $fillable = ['permit_application_id', 'stage', 'approver_title', 'approver_user_id'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
