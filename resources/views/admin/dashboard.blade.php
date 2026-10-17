@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h4 class="fw-bold mb-4">Dashboard Admin</h4>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold">{{ $totalPlayers }}</div><div class="small text-muted">Pemain</div></div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold">{{ $openRooms }}</div><div class="small text-muted">Room Terbuka</div></div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold text-danger">{{ $disputedRooms }}</div><div class="small text-muted">Sengketa</div></div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold">{{ $totalVenues }}</div><div class="small text-muted">Tempat</div></div></div>
        </div>
        <div class="col-6 col-md">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold">{{ $ongoingTournaments }}</div><div class="small text-muted">Turnamen Berjalan</div></div></div>
        </div>
    </div>

    <h6 class="text-uppercase text-muted fw-semibold small mb-2">Kelola Data</h6>
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-dark w-100 py-3"><i class="bi bi-people"></i> Pemain</a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.venues.index') }}" class="btn btn-outline-dark w-100 py-3"><i class="bi bi-geo-alt"></i> Tempat</a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.tournaments.index') }}" class="btn btn-outline-dark w-100 py-3"><i class="bi bi-trophy"></i> Turnamen</a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.vouchers.index') }}" class="btn btn-outline-dark w-100 py-3"><i class="bi bi-ticket-perforated"></i> Voucher</a>
        </div>
    </div>

    <h6 class="text-uppercase text-muted fw-semibold small mb-2">Transaksi &amp; Pengawasan</h6>
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.redemptions.index') }}" class="btn btn-outline-dark w-100 py-3"><i class="bi bi-clock-history"></i> Riwayat Penukaran</a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-dark w-100 py-3"><i class="bi bi-flag"></i> Sengketa Room</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Room Terbaru</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Kode</th><th>Penantang</th><th>Lawan</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($recentRooms as $room)
                        <tr>
                            <td><code>{{ $room->code }}</code></td>
                            <td>{{ $room->challenger->name }}</td>
                            <td>{{ $room->opponent->name ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $room->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">Belum ada room.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
