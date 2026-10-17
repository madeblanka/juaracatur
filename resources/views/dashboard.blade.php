@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('landing') }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-house"></i> Ke Halaman Utama</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Poin Kamu</div>
                    <a href="{{ route('points.history') }}" class="fs-2 fw-bold text-success text-decoration-none d-block" title="Riwayat poin">{{ number_format($user->points) }}</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Tantangan Terbuka</div>
                    <div class="fs-2 fw-bold">{{ $openChallenges->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Mulai Duel</div>
                        <div class="fw-semibold">Tantang pemain lain sekarang</div>
                    </div>
                    <a href="{{ route('rooms.create') }}" class="btn btn-dark">+ Room</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Room Saya</div>
                <div class="list-group list-group-flush">
                    @forelse ($myRooms as $room)
                        <a href="{{ route('rooms.show', $room) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $room->challenger->name }} vs {{ $room->opponent->name ?? 'Menunggu lawan' }}</div>
                                <div class="small text-muted">{{ $room->venue->name }} &middot; {{ $room->wager_points }} poin</div>
                            </div>
                            <span class="badge bg-{{ match($room->status) { 'open' => 'secondary', 'accepted' => 'primary', 'completed' => 'success', 'disputed' => 'danger', default => 'dark' } }}">{{ $room->status }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Belum ada room. Yuk buat tantangan pertamamu!</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Tantangan Terbuka untuk Diterima</div>
                <div class="list-group list-group-flush">
                    @forelse ($openChallenges as $room)
                        <a href="{{ route('rooms.show', $room) }}" class="list-group-item list-group-item-action">
                            <div class="fw-semibold">{{ $room->challenger->name }} &middot; {{ $room->wager_points }} poin</div>
                            <div class="small text-muted">{{ $room->venue->name }}</div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Belum ada tantangan terbuka.</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Riwayat Poin</div>
                <div class="list-group list-group-flush">
                    @forelse ($transactions as $tx)
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="small">{{ $tx->description ?? $tx->type }}</span>
                            <span class="fw-semibold {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ $tx->amount }}</span>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">Belum ada riwayat poin.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
