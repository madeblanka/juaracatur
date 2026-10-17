@extends('layouts.app')

@section('title', 'Riwayat Poin & Notifikasi')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-bold mb-0">Riwayat Poin &amp; Notifikasi</h4>
            <span class="small text-muted">{{ $player->name }} &middot; saldo {{ number_format($player->points) }} poin</span>
        </div>
        <a href="{{ route('admin.users.show', $player) }}" class="btn btn-outline-secondary btn-sm">Kembali ke Detail</a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold">Riwayat Poin</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Waktu</th><th>Jenis</th><th>Keterangan</th><th>Room</th><th>Perubahan</th><th>Saldo Setelahnya</th></tr></thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        <tr>
                            <td class="small text-muted text-nowrap">{{ $tx->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td><code class="small">{{ $tx->type }}</code></td>
                            <td>{{ $tx->description ?? '-' }}</td>
                            <td>@if ($tx->room)<a href="{{ route('rooms.show', $tx->room) }}">{{ $tx->room->code }}</a>@else<span class="text-muted">-</span>@endif</td>
                            <td class="fw-semibold {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }}</td>
                            <td>{{ number_format($tx->balance_after) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Belum ada riwayat poin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mb-4">{{ $transactions->appends(request()->except('poin'))->links() }}</div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Notifikasi Pemain</div>
        <ul class="list-group list-group-flush">
            @forelse ($notifications as $n)
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <div>{{ $n->data['message'] ?? '-' }}</div>
                        <div class="small text-muted">{{ $n->created_at->translatedFormat('d M Y H:i') }}</div>
                    </div>
                    <span class="badge bg-{{ $n->read_at ? 'light text-dark' : 'danger' }} text-nowrap">{{ $n->read_at ? 'Sudah dibaca' : 'Belum dibaca' }}</span>
                </li>
            @empty
                <li class="list-group-item text-muted">Belum ada notifikasi.</li>
            @endforelse
        </ul>
    </div>
    <div class="mt-3">{{ $notifications->appends(request()->except('notif'))->links() }}</div>
@endsection
