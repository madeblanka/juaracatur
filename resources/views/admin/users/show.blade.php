@extends('layouts.app')

@section('title', 'Detail Pemain')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $player->name }}</h4>
            <span class="badge bg-{{ match($player->role) { 'vendor' => 'info text-dark', default => 'light text-dark' } }}">{{ $player->role }}</span>
            <span class="badge bg-{{ $player->is_active ? 'success' : 'secondary' }}">{{ $player->is_active ? 'Aktif' : 'Nonaktif' }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.activity', $player) }}" class="btn btn-outline-dark">Riwayat Poin &amp; Notifikasi</a>
            <a href="{{ route('admin.users.edit', $player) }}" class="btn btn-outline-dark">Edit Data</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Data Pribadi</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between gap-2"><span class="text-muted">Email</span><span class="text-end text-break">{{ $player->email }}</span></li>
                    <li class="list-group-item d-flex justify-content-between gap-2"><span class="text-muted">Nomor HP</span><span class="text-end text-break">{{ $player->phone ?: 'Belum diisi' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between gap-2"><span class="text-muted">Tanggal Lahir</span><span class="text-end text-break">{{ $player->birthdate?->translatedFormat('d F Y') ?? 'Belum diisi' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between gap-2"><span class="text-muted">Usia</span><span class="text-end text-break">{{ $player->usia() !== null ? $player->usia().' tahun' : 'Belum diisi' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between gap-2"><span class="text-muted">Domisili</span><span class="text-end text-break">{{ $player->domisiliLabel() ?: 'Belum diisi' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between gap-2"><span class="text-muted">Bergabung</span><span class="text-end text-break">{{ $player->created_at->translatedFormat('d M Y') }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Statistik</div>
                <div class="card-body">
                    <div class="row text-center g-2">
                        <div class="col-6 col-md-3"><div class="fs-4 fw-bold text-success"><x-points-link :user="$player" /></div><div class="small text-muted">Poin</div></div>
                        <div class="col-6 col-md-3"><div class="fs-4 fw-bold">{{ $wins }}</div><div class="small text-muted">Menang</div></div>
                        <div class="col-6 col-md-3"><div class="fs-4 fw-bold">{{ $totalMatches - $wins }}</div><div class="small text-muted">Kalah</div></div>
                        <div class="col-6 col-md-3"><div class="fs-4 fw-bold">{{ $totalMatches }}</div><div class="small text-muted">Total Duel</div></div>
                    </div>
                    <div class="small text-muted mt-3">Turnamen diikuti: {{ $tournaments->count() }}</div>
                    @if ($tournaments->isNotEmpty())
                        <ul class="small mb-0 mt-1">
                            @foreach ($tournaments as $p)
                                <li><a href="{{ route('admin.tournaments.show', $p->tournament) }}">{{ $p->tournament->name }}</a> &middot; {{ $p->wins }}M / {{ $p->losses }}K / {{ $p->draws }}S</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold">Riwayat Room (20 terakhir)</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Kode</th><th>Lawan</th><th>Tempat</th><th>Taruhan</th><th>Status</th><th>Pemenang</th></tr></thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr>
                            <td><a href="{{ route('rooms.show', $room) }}"><code>{{ $room->code }}</code></a></td>
                            <td>{{ $room->challenger_id === $player->id ? ($room->opponent->name ?? 'Menunggu lawan') : $room->challenger->name }}</td>
                            <td>{{ $room->venue->name }}</td>
                            <td>{{ $room->wager_points }}</td>
                            <td>{{ $room->status }}</td>
                            <td>{{ $room->winner->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Belum pernah bertanding.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Mutasi Poin (20 terakhir)</div>
                <ul class="list-group list-group-flush">
                    @forelse ($transactions as $tx)
                        <li class="list-group-item d-flex justify-content-between small">
                            <span>{{ $tx->description ?? $tx->type }} <span class="text-muted">&middot; {{ $tx->created_at->translatedFormat('d M Y H:i') }}</span></span>
                            <span class="fw-semibold {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ $tx->amount }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small">Belum ada mutasi poin.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Penukaran Poin (20 terakhir)</div>
                <ul class="list-group list-group-flush">
                    @forelse ($redemptions as $r)
                        <li class="list-group-item d-flex justify-content-between small">
                            <span>{{ $r->item_name }} <span class="text-muted">&middot; {{ $r->created_at->translatedFormat('d M Y') }}</span></span>
                            <span>{{ $r->status === 'selesai' ? 'Selesai' : 'Menunggu' }}@if ($r->voucher_code) &middot; <code>{{ $r->voucher_code }}</code>@endif</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small">Belum ada penukaran.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
