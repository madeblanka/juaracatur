@extends('layouts.app')

@section('title', 'Riwayat Poin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Riwayat Poin</h4>
            <span class="small text-muted">Saldo saat ini: <strong>{{ number_format($user->points) }}</strong> poin</span>
        </div>
        <a href="{{ route('notifications.index') }}" class="btn btn-outline-dark btn-sm">Lihat Notifikasi</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Waktu</th><th>Keterangan</th><th>Perubahan</th><th>Saldo Setelahnya</th></tr></thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        <tr>
                            <td class="small text-muted text-nowrap">{{ $tx->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>
                                {{ $tx->description ?? $tx->type }}
                                @if ($tx->room)
                                    <div class="small"><a href="{{ route('rooms.show', $tx->room) }}">Room {{ $tx->room->code }}</a></div>
                                @endif
                            </td>
                            <td class="fw-semibold {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }}</td>
                            <td>{{ number_format($tx->balance_after) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">Belum ada riwayat poin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $transactions->links() }}</div>
@endsection
