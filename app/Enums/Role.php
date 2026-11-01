<?php

namespace App\Enums;

enum Role: string
{
    case Applicant = 'applicant';
    case Admin = 'admin';
    case Approver = 'approver';
    case Management = 'management';
    case SystemAdmin = 'system-admin';

    public function label(): string
    {
        return match ($this) {
            self::Applicant => 'Applicant',
            self::Admin => 'Admin / Verifier',
            self::Approver => 'Approver',
            self::Management => 'Management',
            self::SystemAdmin => 'System administrator',
        };
    }

    /** Nama route halaman utama tiap role (dipakai setelah login). */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Applicant => 'applicant.dashboard',
            self::Admin => 'admin.queues',
            self::Approver => 'approver.queue',
            self::Management => 'management.index',
            self::SystemAdmin => 'system-admin.dashboard',
        };
    }
}
