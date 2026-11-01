{{-- $application dengan relasi histories & approvalSteps.approver --}}
<article class="timeline-card application-timeline">
    <div class="section-heading compact-heading">
        <div><div class="section-kicker">RIWAYAT PENGAJUAN</div><h2>Pelacakan status · {{ $application->number }}</h2><p>Riwayat perubahan status dari audit pengajuan.</p></div>
        @include('partials.status-badge', ['status' => $application->status])
    </div>
    <ol class="timeline">
        @forelse ($application->histories as $h)
            @php $step = ($h->status === \App\Enums\ApplicationStatus::InApproval && $h->stage) ? $application->approvalSteps->firstWhere('stage', $h->stage) : null; @endphp
            <li class="timeline-item {{ $loop->last ? 'is-current' : 'is-complete' }}">
                <span class="timeline-marker"><i class="fa-solid {{ $loop->last ? 'fa-clock' : 'fa-check' }}"></i></span>
                <div>
                    <div class="timeline-title">
                        {{ $step ? 'Persetujuan tahap ' . $h->stage : $h->status->label() }}
                        <time>{{ $h->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y · H:i') }} WIB</time>
                    </div>
                    <p>{{ $h->actor_name }} · {{ $h->actor_role }}{{ $step ? ' · ' . $step->approver_title . ' (' . $step->approver?->name . ')' : '' }}</p>
                    @include('partials.status-badge', ['status' => $h->status])
                    @if ($h->decision)<p>Keputusan: {{ $h->decision }}</p>@endif
                    @if ($h->comment)<p>Catatan: {{ $h->comment }}</p>@endif
                </div>
            </li>
        @empty
            <li class="timeline-item"><span class="timeline-marker"></span><div><div class="timeline-title">Belum ada riwayat</div><p>Perubahan status akan ditampilkan di sini.</p></div></li>
        @endforelse
    </ol>
    @if ($application->approvalSteps->isNotEmpty())
        <div class="workflow-path">
            <strong>Jalur persetujuan</strong>
            <ol>
                @foreach ($application->approvalSteps as $step)
                    <li>Tahap {{ $step->stage }}: {{ $step->approver_title }} ({{ $step->approver?->name ?? 'akun approver' }})</li>
                @endforeach
            </ol>
        </div>
    @endif
    @if ($application->correction_note)
        <div class="timeline-feedback">{{ $application->correction_note }}</div>
    @endif
</article>
