<?php

namespace App\Support;

use App\Enums\ApplicationStatus as S;
use App\Enums\Role;
use App\Models\PermitApplication;
use App\Models\User;

/** Angka lencana di sidebar (antrean Admin dan Approver). */
class NavCounts
{
    public static function for(User $user): int
    {
        return match ($user->role) {
            Role::Admin => PermitApplication::withStatus(S::Submitted, S::Verification, S::Correction, S::Approved)->count(),
            Role::Approver => PermitApplication::awaitingApprover($user)->count(),
            default => 0,
        };
    }
}
