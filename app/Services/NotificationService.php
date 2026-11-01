<?php

namespace App\Services;

use App\Enums\ApplicationStatus as S;
use App\Enums\Role;
use App\Models\ApplicationHistory;
use App\Models\PermitApplication;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Notifikasi turunan dari data (sama dengan renderNotifications di sik.js).
 * Status "sudah dibaca" disimpan per pengguna di users.notifications_read_at.
 */
class NotificationService
{
    /** @return Collection<int, array{id:string,text:string,time:\Illuminate\Support\Carbon,url:string,unread:bool}> */
    public function for(User $user): Collection
    {
        $items = collect();

        switch ($user->role) {
            case Role::Applicant:
                ApplicationHistory::query()
                    ->whereIn('status', [S::Revision->value, S::Issued->value, S::Rejected->value, S::Closed->value])
                    ->whereHas('application', fn ($q) => $q->where('user_id', $user->id))
                    ->with('application')->latest('created_at')->limit(30)->get()
                    ->each(function (ApplicationHistory $h) use ($items) {
                        $text = "{$h->application->number} · {$h->status->label()}";
                        if ($h->status === S::Revision && $h->comment) {
                            $text .= ": {$h->comment}";
                        }
                        $items->push(['id' => "h{$h->id}", 'text' => $text, 'time' => $h->created_at,
                            'url' => route('applicant.applications.show', $h->permit_application_id)]);
                    });
                break;

            case Role::Admin:
                PermitApplication::withStatus(S::Submitted, S::Correction, S::Approved)->latest('updated_at')->limit(30)->get()
                    ->each(fn ($a) => $items->push([
                        'id' => "a{$a->id}:{$a->status->value}",
                        'text' => "{$a->number} · {$a->status->label()} menunggu tindakan Admin",
                        'time' => $a->updated_at, 'url' => route('admin.queues') . '#' . match ($a->status) {
                            S::Correction => 'correction', S::Approved => 'issuance', default => 'submitted'},
                    ]));
                break;

            case Role::Approver:
                PermitApplication::awaitingApprover($user)->latest('updated_at')->limit(30)->get()
                    ->each(fn ($a) => $items->push([
                        'id' => "a{$a->id}:s{$a->current_stage}",
                        'text' => "{$a->number} · menunggu persetujuan Tahap {$a->current_stage}",
                        'time' => $a->updated_at, 'url' => route('approver.queue'),
                    ]));
                break;

            default:
                $inApproval = PermitApplication::withStatus(S::InApproval)->count();
                $rejected = PermitApplication::withStatus(S::Rejected)->count();
                if ($inApproval || $rejected) {
                    $items->push([
                        'id' => "summary:{$inApproval}:{$rejected}",
                        'text' => "{$inApproval} pengajuan " . S::InApproval->label() . " · {$rejected} pengajuan " . S::Rejected->label(),
                        'time' => now(),
                        'url' => route($user->role->homeRoute()),
                    ]);
                }
        }

        $readAt = $user->notifications_read_at;

        return $items->sortByDesc('time')->values()
            ->map(fn ($n) => $n + ['unread' => ! $readAt || $n['time']->gt($readAt)]);
    }

    public function markAllRead(User $user): void
    {
        $user->forceFill(['notifications_read_at' => now()])->save();
    }
}
