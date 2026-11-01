<?php

namespace App\Services;

use App\Enums\ApplicationStatus as S;
use App\Enums\Role;
use App\Models\ApplicationHistory;
use App\Models\PermitApplication;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Support\WorkflowException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Seluruh transisi status SIK. Semua guard di sini diulang di server (bukan hanya di UI):
 * status yang sah, tahap approver berjalan, catatan wajib, templat aktif, nomor SIK unik.
 *
 * DRAFT/REVISION -> SUBMITTED -> VERIFICATION -> IN_APPROVAL (tahap 1..N) -> APPROVED -> ISSUED
 *   VERIFICATION -> REVISION (kembali ke pemohon)
 *   IN_APPROVAL  -> CORRECTION (return) -> IN_APPROVAL (koreksi Admin, tahap sama) | CLOSED
 *   IN_APPROVAL  -> REJECTED
 */
class ApplicationWorkflowService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ApplicationDataService $data,
    ) {}

    // ---------------------------------------------------------------- Pemohon

    /** Draf baru (belum ada record). */
    public function createDraft(User $actor): PermitApplication
    {
        return DB::transaction(function () use ($actor) {
            $application = PermitApplication::create([
                'number' => $this->data->nextApplicationNumber(),
                'user_id' => $actor->id,
                'contact_name' => $actor->name,
                'status' => S::Draft,
            ]);
            $this->history($application, $actor, S::Draft, 'Simpan draf', 'Draf baru dibuat.');
            $this->audit->record($actor, 'Draf dibuat', $application->number);

            return $application;
        });
    }

    public function saveDraft(PermitApplication $application, User $actor, Request $request): PermitApplication
    {
        return DB::transaction(function () use ($application, $actor, $request) {
            $application = $this->lock($application);
            $this->assertStatus($application, [S::Draft, S::Revision], 'Pengajuan hanya dapat diedit saat berstatus DRAFT atau REVISION.');
            $this->data->fill($application, $request);
            $this->audit->record($actor, 'Draf disimpan', $application->number);

            return $application;
        });
    }

    public function submit(PermitApplication $application, User $actor, Request $request): PermitApplication
    {
        return DB::transaction(function () use ($application, $actor, $request) {
            $application = $this->lock($application);
            $this->assertStatus($application, [S::Draft, S::Revision], 'Pengajuan hanya dapat dikirim saat berstatus DRAFT atau REVISION.');
            $this->data->fill($application, $request);
            $this->transition($application, $actor, S::Submitted, 'Kirim pengajuan', null, extra: [
                'submitted_at' => now(), 'correction_note' => null,
            ]);

            return $application;
        });
    }

    // ------------------------------------------------------------ Admin / Verifier

    public function startVerification(PermitApplication $application, User $actor): PermitApplication
    {
        return DB::transaction(function () use ($application, $actor) {
            $application = $this->lock($application);
            // Idempoten: "Lanjutkan verifikasi" pada pengajuan yang sudah VERIFICATION tidak mengubah apa pun.
            if ($application->status === S::Verification) {
                return $application;
            }
            $this->assertStatus($application, [S::Submitted], 'Verifikasi hanya dapat dimulai dari status SUBMITTED.');
            $this->transition($application, $actor, S::Verification, 'Mulai verifikasi', 'Admin memulai pemeriksaan dokumen.');

            return $application;
        });
    }

    /** Teruskan ke persetujuan; alur diambil dari templat aktif Area + Jenis pekerjaan (bukan dari klien). */
    public function forwardToApproval(PermitApplication $application, User $actor, ?string $note): PermitApplication
    {
        return DB::transaction(function () use ($application, $actor, $note) {
            $application = $this->lock($application);
            $this->assertStatus($application, [S::Verification], 'Pengajuan harus berstatus VERIFICATION untuk diteruskan.');

            $template = WorkflowTemplate::resolve($application->area_id, $application->work_type_id);
            if (! $template || $template->steps->isEmpty()) {
                $area = $application->area?->name ?? '—';
                $type = $application->workType?->name ?? '—';
                throw new WorkflowException("Templat alur untuk {$area} + {$type} belum tersedia atau tidak aktif.");
            }

            $this->snapshotSteps($application, $template);
            $this->transition($application, $actor, S::InApproval, 'Teruskan ke persetujuan',
                $note ?: 'Data dan dokumen diverifikasi; diteruskan ke alur persetujuan.',
                stage: 1, extra: ['current_stage' => 1, 'total_stages' => $template->steps->count()]);

            return $application;
        });
    }

    public function returnForRevision(PermitApplication $application, User $actor, string $note): PermitApplication
    {
        return DB::transaction(function () use ($application, $actor, $note) {
            $application = $this->lock($application);
            $this->assertStatus($application, [S::Verification], 'Pengajuan tidak berada pada tahap verifikasi.');
            $this->transition($application, $actor, S::Revision, 'Kembalikan untuk REVISION', $note,
                extra: ['correction_note' => $note]);

            return $application;
        });
    }

    /** Admin memperbaiki data lalu mengirim kembali ke approver pada tahap yang sama (BR-18). */
    public function applyCorrection(PermitApplication $application, User $actor, Request $request, string $note): PermitApplication
    {
        return DB::transaction(function () use ($application, $actor, $request, $note) {
            $application = $this->lock($application);
            $this->assertStatus($application, [S::Correction], 'Pengajuan ini tidak lagi berada pada antrean koreksi Admin.');

            $newAreaId = (int) $request->input('area');
            $newTypeId = (int) $request->input('workType');
            $workflowChanged = $newAreaId !== (int) $application->area_id || $newTypeId !== (int) $application->work_type_id;

            $template = null;
            if ($workflowChanged) {
                $template = WorkflowTemplate::resolve($newAreaId, $newTypeId);
                if (! $template || $template->steps->isEmpty()) {
                    throw new WorkflowException('Tidak ada templat aktif untuk kombinasi Area + Jenis pekerjaan yang dipilih. Pilih kombinasi yang memiliki templat.');
                }
            }

            $originalStage = max(1, (int) $application->current_stage);
            $this->data->fill($application, $request);

            $stage = $originalStage;
            $warning = '';
            if ($template) {
                $this->snapshotSteps($application, $template);
                $application->total_stages = $template->steps->count();
                if ($originalStage > $application->total_stages) {
                    $stage = $application->total_stages;
                    $warning = " Tahap sebelumnya melebihi alur baru; pengajuan diarahkan ke tahap terakhir ({$stage}).";
                }
            }

            $this->transition($application, $actor, S::InApproval, 'Koreksi Admin', $note . $warning,
                stage: $stage, extra: ['current_stage' => $stage, 'correction_note' => $note]);

            return $application;
        });
    }

    /** Surat permohonan tidak valid: tutup, pemohon harus mengajukan ulang dari awal. */
    public function closeInvalid(PermitApplication $application, User $actor, string $reason): PermitApplication
    {
        return DB::transaction(function () use ($application, $actor, $reason) {
            $application = $this->lock($application);
            $this->assertStatus($application, [S::Correction], 'Pengajuan hanya dapat ditutup dari status CORRECTION.');
            $this->transition($application, $actor, S::Closed, 'Tutup pengajuan', $reason,
                extra: ['correction_note' => $reason]);

            return $application;
        });
    }

    public function issue(PermitApplication $application, User $actor, string $permitNumber, string $issueDate): PermitApplication
    {
        try {
            return DB::transaction(function () use ($application, $actor, $permitNumber, $issueDate) {
                $application = $this->lock($application);
                $this->assertStatus($application, [S::Approved], 'SIK hanya dapat diterbitkan untuk pengajuan berstatus APPROVED.');
                $this->transition($application, $actor, S::Issued, 'Terbitkan SIK',
                    "SIK {$permitNumber} diterbitkan pada {$issueDate}.",
                    extra: ['permit_number' => $permitNumber, 'issue_date' => $issueDate]);

                return $application;
            });
        } catch (UniqueConstraintViolationException) {
            // Constraint UNIQUE di DB adalah pagar terakhir bila dua Admin menerbitkan nomor yang sama bersamaan.
            throw ValidationException::withMessages(['permitNumber' => 'Nomor SIK sudah digunakan.']);
        }
    }

    /** Usulan nomor SIK berikutnya: SIK/AYANI/YYYY/NNN. */
    public function suggestPermitNumber(): string
    {
        $year = now('Asia/Jakarta')->format('Y');
        $last = PermitApplication::where('permit_number', 'like', "SIK/AYANI/{$year}/%")
            ->orderByDesc('permit_number')->value('permit_number');
        $next = $last ? (int) substr($last, -3) + 1 : 1;

        return sprintf('SIK/AYANI/%s/%03d', $year, $next);
    }

    // -------------------------------------------------------------- Approver

    /** @param 'approve'|'return'|'reject' $decision */
    public function decide(PermitApplication $application, User $actor, string $decision, ?string $comment): PermitApplication
    {
        return DB::transaction(function () use ($application, $actor, $decision, $comment) {
            $application = $this->lock($application);
            $this->assertStatus($application, [S::InApproval], 'Pengajuan tidak sedang dalam persetujuan.');

            if (! $actor->hasRole(Role::Approver) || ! $actor->is_active) {
                throw new WorkflowException('Hanya akun Approver aktif yang dapat memberi keputusan.');
            }
            $step = $application->approvalSteps()->where('stage', $application->current_stage)->first();
            // Penugasan dibaca dari penyimpanan server, bukan dari data kiriman klien.
            if (! $step || (int) $step->approver_user_id !== (int) $actor->id) {
                throw new WorkflowException('Pengajuan ini bukan bagian dari antrean tahap Anda.');
            }
            $comment = trim((string) $comment);
            if (in_array($decision, ['return', 'reject'], true) && $comment === '') {
                throw ValidationException::withMessages(['comment' => 'Catatan wajib diisi untuk pengembalian atau penolakan.']);
            }

            $stage = (int) $application->current_stage;
            $label = strtoupper($decision);

            if ($decision === 'approve') {
                if ($stage >= $application->total_stages) {
                    $this->transition($application, $actor, S::Approved, $label,
                        $comment ?: "Persetujuan tahap akhir ({$stage} dari {$application->total_stages}) selesai.", stage: $stage);
                } else {
                    $this->transition($application, $actor, S::InApproval, $label,
                        $comment ?: "Tahap {$stage} disetujui; diteruskan ke approver tahap " . ($stage + 1) . '.',
                        stage: $stage, extra: ['current_stage' => $stage + 1]);
                }
            } elseif ($decision === 'return') {
                $this->transition($application, $actor, S::Correction, $label, $comment,
                    stage: $stage, extra: ['correction_note' => $comment]);
            } else {
                $this->transition($application, $actor, S::Rejected, $label, $comment,
                    stage: $stage, extra: ['correction_note' => $comment]);
            }

            return $application;
        });
    }

    // --------------------------------------------------------------- Internal

    private function lock(PermitApplication $application): PermitApplication
    {
        return PermitApplication::query()->lockForUpdate()->findOrFail($application->id);
    }

    /** @param S[] $allowed */
    private function assertStatus(PermitApplication $application, array $allowed, string $message): void
    {
        if (! in_array($application->status, $allowed, true)) {
            throw new WorkflowException($message);
        }
    }

    private function snapshotSteps(PermitApplication $application, WorkflowTemplate $template): void
    {
        $application->approvalSteps()->delete();
        foreach ($template->steps as $step) {
            $application->approvalSteps()->create([
                'stage' => $step->stage,
                'approver_title' => $step->approver_title,
                'approver_user_id' => $step->approver_user_id,
            ]);
        }
        $application->unsetRelation('approvalSteps');
    }

    private function transition(
        PermitApplication $application,
        User $actor,
        S $status,
        string $decision,
        ?string $comment,
        ?int $stage = null,
        array $extra = [],
    ): void {
        $application->forceFill(['status' => $status] + $extra)->save();
        $this->history($application, $actor, $status, $decision, $comment, $stage ?? (int) $application->current_stage);
        $this->audit->record($actor, "Status menjadi {$status->value}", $application->number, $comment);
    }

    private function history(PermitApplication $application, User $actor, S $status, ?string $decision, ?string $comment, ?int $stage = null): void
    {
        ApplicationHistory::create([
            'permit_application_id' => $application->id,
            'status' => $status->value,
            'stage' => $stage ?? (int) $application->current_stage,
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'actor_role' => $actor->role->label(),
            'decision' => $decision,
            'comment' => $comment,
        ]);
    }
}
