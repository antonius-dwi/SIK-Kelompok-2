{{-- $histories: koleksi ApplicationHistory --}}
<div class="table-responsive role-table-wrap">
    <table class="table role-table">
        <thead><tr><th>WAKTU (WIB)</th><th>TAHAP</th><th>AKTOR / ROLE</th><th>KEPUTUSAN</th><th>KOMENTAR</th></tr></thead>
        <tbody>
        @forelse ($histories as $h)
            <tr>
                <td>{{ $h->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB</td>
                <td>{{ $h->stage ? 'Tahap ' . $h->stage : '—' }}</td>
                <td>{{ $h->actor_name }} · {{ $h->actor_role }}</td>
                <td>{{ $h->decision ?: '—' }}</td>
                <td>{{ $h->comment ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Belum ada riwayat untuk pengajuan ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
