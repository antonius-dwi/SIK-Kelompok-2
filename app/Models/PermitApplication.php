<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermitApplication extends Model
{
    protected $fillable = [
        'number', 'user_id', 'applicant_name', 'contact_name', 'job_title', 'work_description', 'area_id', 'work_type_id',
        'start_date', 'end_date', 'status', 'current_stage', 'total_stages', 'correction_note',
        'permit_number', 'issue_date', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'issue_date' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(ApplicationApprovalStep::class)->orderBy('stage');
    }

    public function personnel(): HasMany
    {
        return $this->hasMany(ApplicationPersonnel::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ApplicationItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApplicationHistory::class)->orderBy('created_at')->orderBy('id');
    }

    public function currentStep(): ?ApplicationApprovalStep
    {
        if ($this->current_stage < 1) {
            return null;
        }

        return $this->approvalSteps->firstWhere('stage', $this->current_stage)
            ?? $this->approvalSteps()->where('stage', $this->current_stage)->first();
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->status === ApplicationStatus::InApproval
            && (int) $this->currentStep()?->approver_user_id === (int) $user->id;
    }

    /**
     * Pengajuan yang sedang menunggu keputusan approver tertentu pada tahap berjalan.
     * Tanpa pengguna (null) hasilnya kosong — fail-closed, bukan menampilkan semua pengajuan.
     */
    public function scopeAwaitingApprover($query, ?User $user)
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0'); // tanpa pengguna: tidak ada baris
        }

        return $query->where('status', ApplicationStatus::InApproval->value)
            ->whereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')->from('application_approval_steps as s')
                    ->whereColumn('s.permit_application_id', 'permit_applications.id')
                    ->whereColumn('s.stage', 'permit_applications.current_stage')
                    ->where('s.approver_user_id', $user->id);
            });
    }

    /** Pengajuan milik pengguna; tanpa pengguna (null) hasilnya kosong, bukan semua pengajuan. */
    public function scopeOwnedBy($query, ?User $user)
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0'); // tanpa pengguna: tidak ada baris
        }

        return $query->where('user_id', $user->id);
    }

    public function scopeWithStatus($query, ApplicationStatus ...$statuses)
    {
        return $query->whereIn('status', array_map(fn ($s) => $s->value, $statuses));
    }

    /**
     * Uraian/tujuan pekerjaan untuk tampilan & surat SIK.
     * Memakai `work_description` saja; `job_title` tidak lagi dipakai sebagai cadangan.
     */
    public function workSubject(): string
    {
        return trim((string) $this->work_description);
    }

    /** Ringkasan barang untuk tabel, mis. "Bor listrik (2; IN) · Barrier (4; OUT)". */
    public function itemsSummary(): string
    {
        return $this->items->map(fn ($i) => "{$i->name} ({$i->quantity}; {$i->movement})")->implode(' · ');
    }
}
