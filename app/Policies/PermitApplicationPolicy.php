<?php

namespace App\Policies;

use App\Enums\ApplicationStatus as S;
use App\Enums\Role;
use App\Models\PermitApplication;
use App\Models\User;

/**
 * Otorisasi objek per record: cegah akses lintas pemohon dan aksi di luar role/tahap.
 */
class PermitApplicationPolicy
{
    public function view(User $user, PermitApplication $application): bool
    {
        return match ($user->role) {
            Role::Applicant => (int) $application->user_id === (int) $user->id,
            Role::Admin, Role::Management, Role::SystemAdmin => true,
            // Approver boleh melihat bila ia ditugaskan pada salah satu tahap alur pengajuan tersebut.
            Role::Approver => $application->approvalSteps()->where('approver_user_id', $user->id)->exists(),
        };
    }

    /** Hanya pemilik, dan hanya saat DRAFT/REVISION. */
    public function update(User $user, PermitApplication $application): bool
    {
        return $user->hasRole(Role::Applicant)
            && (int) $application->user_id === (int) $user->id
            && $application->status->isApplicantEditable();
    }

    public function verify(User $user, PermitApplication $application): bool
    {
        return $user->hasRole(Role::Admin)
            && in_array($application->status, [S::Submitted, S::Verification], true);
    }

    public function correct(User $user, PermitApplication $application): bool
    {
        return $user->hasRole(Role::Admin) && $application->status === S::Correction;
    }

    public function issue(User $user, PermitApplication $application): bool
    {
        return $user->hasRole(Role::Admin) && $application->status === S::Approved;
    }

    public function decide(User $user, PermitApplication $application): bool
    {
        return $user->hasRole(Role::Approver) && $user->is_active && $application->isAssignedTo($user);
    }

    /** Cetak / unduh SIK: pemohon pemilik, Admin, atau Management setelah diterbitkan. */
    public function printPermit(User $user, PermitApplication $application): bool
    {
        /* Sudah terbit: siapa pun yang boleh melihat pengajuan boleh mencetak SIK-nya.
           Belum terbit: admin yang berwenang menerbitkan boleh mempratinjau/mengunduh
           dokumen dengan template yang sama (nomor & tanggal isian dipakai sebagai data
           pratinjau, tidak disimpan ke database). */
        return ($application->status === S::Issued && $this->view($user, $application))
            || $this->issue($user, $application);
    }
}
