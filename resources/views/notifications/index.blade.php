@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Notifikasi</h4>
        @if ($notifications->contains(fn ($n) => $n->read_at === null))
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="btn btn-sm btn-outline-dark">Tandai Semua Dibaca</button>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="list-group-item {{ $notification->read_at ? '' : 'bg-light' }}">
                    @csrf
                    <button type="submit" class="btn btn-link text-decoration-none text-dark p-0 text-start w-100 d-flex justify-content-between align-items-center">
                        <span>
                            @if (! $notification->read_at)
                                <span class="badge bg-danger me-2">Baru</span>
                            @endif
                            {{ $notification->data['message'] ?? '' }}
                        </span>
                        <span class="small text-muted text-nowrap ms-2">{{ $notification->created_at->diffForHumans() }}</span>
                    </button>
                </form>
            @empty
                <div class="list-group-item text-muted">Belum ada notifikasi.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">{{ $notifications->links() }}</div>
@endsection
