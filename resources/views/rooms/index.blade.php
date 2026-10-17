@extends('layouts.app')

@section('title', 'Room Tantangan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Room Tantangan</h4>
        <a href="{{ route('rooms.create') }}" class="btn btn-dark">+ Buat Tantangan</a>
    </div>

    <h6 class="text-muted mb-3">Tantangan Terbuka</h6>
    <div class="row g-3 mb-5">
        @forelse ($openChallenges as $room)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">{{ $room->challenger->name }}</h6>
                        <p class="small text-muted mb-1"><i class="bi bi-geo-alt"></i> {{ $room->venue->name }}</p>
                        <p class="small text-muted mb-2">
                            @if ($room->opponent_id)
                                Menantang kamu langsung
                            @else
                                Tantangan terbuka untuk siapapun
                            @endif
                        </p>
                        <span class="badge bg-success mb-3">{{ $room->wager_points }} poin</span>
                        <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-outline-dark w-100">Lihat Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Belum ada tantangan terbuka saat ini.</div>
        @endforelse
    </div>

    <h6 class="text-muted mb-3">Room Saya</h6>
    <div class="table-responsive">
        <table class="table bg-white align-middle">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Lawan</th>
                    <th>Tempat</th>
                    <th>Taruhan</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($myRooms as $room)
                    <tr>
                        <td><code>{{ $room->code }}</code></td>
                        <td>{{ $room->challenger_id === auth()->id() ? ($room->opponent->name ?? 'Menunggu lawan') : $room->challenger->name }}</td>
                        <td>{{ $room->venue->name }}</td>
                        <td>{{ $room->wager_points }}</td>
                        <td><span class="badge bg-{{ match($room->status) { 'open' => 'secondary', 'accepted' => 'primary', 'completed' => 'success', 'disputed' => 'danger', default => 'dark' } }}">{{ $room->status }}</span></td>
                        <td><a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-outline-dark">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Kamu belum punya room.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
