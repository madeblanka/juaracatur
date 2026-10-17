@extends('layouts.app')

@section('title', 'Sengketa Room')

@section('content')
    <h4 class="fw-bold mb-4">Room Sengketa &amp; Berlangsung</h4>

    <div class="row g-3">
        @forelse ($rooms as $room)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="fw-bold mb-0">{{ $room->challenger->name }} vs {{ $room->opponent->name ?? '-' }}</h6>
                            <span class="badge bg-{{ $room->status === 'disputed' ? 'danger' : 'primary' }}">{{ strtoupper($room->status) }}</span>
                        </div>
                        <p class="small text-muted mb-2">{{ $room->venue->name }} &middot; {{ $room->wager_points }} poin</p>

                        @if ($room->status === 'disputed')
                            <p class="small mb-2">
                                Laporan {{ $room->challenger->name }}: <strong>{{ $room->challenger_reported_winner_id === $room->challenger_id ? $room->challenger->name : $room->opponent->name }}</strong><br>
                                Laporan {{ $room->opponent->name }}: <strong>{{ $room->opponent_reported_winner_id === $room->challenger_id ? $room->challenger->name : $room->opponent->name }}</strong>
                            </p>
                        @endif

                        @if ($room->opponent)
                            <form method="POST" action="{{ route('admin.rooms.resolve', $room) }}" class="d-flex gap-2">
                                @csrf
                                <select name="winner_id" class="form-select form-select-sm" required>
                                    <option value="">Tentukan pemenang</option>
                                    <option value="{{ $room->challenger_id }}">{{ $room->challenger->name }}</option>
                                    <option value="{{ $room->opponent_id }}">{{ $room->opponent->name }}</option>
                                </select>
                                <button class="btn btn-sm btn-dark text-nowrap">Selesaikan</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Tidak ada room sengketa atau berlangsung saat ini.</div>
        @endforelse
    </div>
@endsection
