<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';
    case Verification = 'VERIFICATION';
    case Revision = 'REVISION';
    case InApproval = 'IN_APPROVAL';
    case Correction = 'CORRECTION';
    case Approved = 'APPROVED';
    case Issued = 'ISSUED';
    case Rejected = 'REJECTED';
    case Closed = 'CLOSED';

    /** Label tampilan (Bahasa Indonesia), sama dengan statusDefinitions di sik.js. */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Diajukan',
            self::Verification => 'Verifikasi',
            self::Revision => 'Revisi',
            self::InApproval => 'Dalam persetujuan',
            self::Correction => 'Koreksi',
            self::Approved => 'Disetujui',
            self::Issued => 'Diterbitkan',
            self::Rejected => 'Ditolak',
            self::Closed => 'Ditutup',
        };
    }

    /** Kelas CSS badge di public/css/sik.css. */
    public function cssClass(): string
    {
        return 'status-' . match ($this) {
            self::InApproval => 'approval',
            default => strtolower($this->value),
        };
    }

    /** Status yang boleh diedit oleh pemohon. */
    public function isApplicantEditable(): bool
    {
        return in_array($this, [self::Draft, self::Revision], true);
    }

    /** Status "dalam proses" untuk kartu ringkasan pemohon. */
    public static function inProgress(): array
    {
        return [self::Submitted, self::Verification, self::Revision, self::InApproval, self::Correction];
    }
}
