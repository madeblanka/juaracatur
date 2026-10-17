@extends('layouts.app')

@section('title', $tournament->name)

@section('content')
    @php
        $routePrefix = explode('.', request()->route()->getName())[0];
        $formatLabel = $tournament->formatLabel();
        $minParticipants = match (true) {
            $tournament->isDoubleElimination() => 4,
            $tournament->isSingleElimination() => 2,
            default => 3,
        };
        $bisaUbahBagan = $tournament->status === 'ongoing' && ! $tournament->matches->contains('status', 'completed');
    @endphp

    <div class="mb-4">
        <h4 class="fw-bold mb-2">{{ $tournament->name }}</h4>
        <span class="badge bg-{{ match($tournament->status) { 'draft' => 'secondary', 'ongoing' => 'primary', 'completed' => 'success', default => 'dark' } }}">{{ strtoupper($tournament->status) }}</span>
        <span class="badge bg-info text-dark">{{ $formatLabel }}</span>
        @if ($tournament->starts_at)
            <span class="badge bg-light text-dark"><i class="bi bi-calendar-event"></i> Mulai {{ $tournament->starts_at->translatedFormat('l, d M Y H:i') }} &middot; jeda {{ $tournament->round_gap_minutes }} menit/ronde</span>
        @endif
        @if ($routePrefix === 'admin' && $tournament->creator)
            <div class="small text-muted mt-1">Dibuat oleh {{ $tournament->creator->name }} ({{ $tournament->creator->isVendor() ? 'vendor' : 'admin' }})</div>
        @endif
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4 col-lg-3">
            <div class="card">
                @if ($tournament->flyerUrl())
                    <img src="{{ $tournament->flyerUrl() }}" alt="Flyer {{ $tournament->name }}" class="card-img-top">
                @else
                    <div class="card-body text-center text-muted small">Belum ada flyer</div>
                @endif
                <div class="card-body">
                    <form method="POST" action="{{ route($routePrefix.'.tournaments.flyer.update', $tournament) }}" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label small fw-semibold">{{ $tournament->flyerUrl() ? 'Ganti flyer' : 'Unggah flyer' }}</label>
                        <input type="file" name="flyer" class="form-control form-control-sm mb-2" accept="image/*" required>
                        <button class="btn btn-sm btn-outline-dark w-100">Simpan Flyer</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-lg-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Deskripsi</span>
                    <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#edit-deskripsi">Edit Deskripsi</button>
                </div>
                <div class="card-body" style="white-space: pre-line;">{{ $tournament->description ?: 'Belum ada deskripsi.' }}</div>
                <div class="collapse border-top" id="edit-deskripsi">
                    <form method="POST" action="{{ route($routePrefix.'.tournaments.description.update', $tournament) }}" class="card-body">
                        @csrf
                        @method('PUT')
                        <textarea name="description" class="form-control mb-2" rows="6" maxlength="1000" placeholder="Contoh:&#10;Hadiah: Rp 500.000&#10;Biaya daftar: gratis">{{ old('description', $tournament->description) }}</textarea>
                        <div class="form-text mb-2">Tulis per baris. Baris baru akan tampil apa adanya di halaman turnamen.</div>
                        <button class="btn btn-dark btn-sm">Simpan Deskripsi</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-geo-alt"></i> Lokasi</span>
                    <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#edit-lokasi">Edit Lokasi</button>
                </div>
                <div class="card-body">
                    @if ($tournament->venue)
                        <div class="fw-semibold">{{ $tournament->venue->name }}</div>
                        <div class="small text-muted">{{ $tournament->venue->address }}</div>
                    @else
                        <div class="text-muted">Belum ada lokasi. Pemain hanya melihat nama turnamen tanpa peta.</div>
                    @endif
                </div>
                <div class="collapse border-top" id="edit-lokasi">
                    <form method="POST" action="{{ route($routePrefix.'.tournaments.venue.update', $tournament) }}" class="card-body">
                        @csrf
                        @method('PUT')
                        <label class="form-label small fw-semibold">Pilih tempat</label>
                        <select name="venue_id" class="form-select mb-2">
                            <option value="">Tanpa lokasi</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}" @selected((int) old('venue_id', $tournament->venue_id) === $venue->id)>{{ $venue->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text mb-2">Peta dan tombol Google Maps di halaman turnamen mengikuti tempat yang dipilih.</div>
                        <button class="btn btn-dark btn-sm">Simpan Lokasi</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-coin"></i> Biaya Pendaftaran</span>
                    @if ($tournament->status === 'draft')
                        <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#edit-biaya">Ubah Biaya</button>
                    @endif
                </div>
                <div class="card-body">
                    @if ($tournament->entry_points > 0)
                        <div class="fw-semibold">{{ number_format($tournament->entry_points) }} poin per pemain</div>
                        <div class="small text-muted">Pemain membayar dengan poinnya sendiri saat mendaftar. Poin dikembalikan jika pemain keluar sebelum bagan dibuat.</div>
                    @else
                        <div class="text-muted">Gratis. Pemain bisa mendaftar tanpa memotong poin.</div>
                    @endif
                </div>
                @if ($tournament->status === 'draft')
                    <div class="collapse border-top" id="edit-biaya">
                        <form method="POST" action="{{ route($routePrefix.'.tournaments.entry.update', $tournament) }}" class="card-body">
                            @csrf
                            @method('PUT')
                            <label class="form-label small fw-semibold" for="input-biaya">Biaya dalam poin (0 = gratis)</label>
                            <input id="input-biaya" type="number" name="entry_points" class="form-control mb-2" min="0" max="100000" value="{{ old('entry_points', $tournament->entry_points) }}" required>
                            <button class="btn btn-dark btn-sm">Simpan Biaya</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            @if ($tournament->status === 'draft')
                <div class="card mb-4">
                    <div class="card-header bg-white fw-semibold">Tambah Peserta <span class="small text-muted fw-normal">(manual, tanpa potong poin)</span></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route($routePrefix.'.tournaments.participants.store', $tournament) }}" class="d-flex gap-2">
                            @csrf
                            <select name="user_id" class="form-select" required>
                                <option value="">Pilih pemain...</option>
                                @foreach ($players as $player)
                                    <option value="{{ $player->id }}">{{ $player->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-dark text-nowrap">Tambah</button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-white fw-semibold">Undang Pemain</div>
                    <div class="card-body">
                        @if ($players->isEmpty())
                            <p class="small text-muted mb-0">Semua pemain aktif sudah menjadi peserta.</p>
                        @else
                            <form method="POST" action="{{ route($routePrefix.'.tournaments.invite', $tournament) }}">
                                @csrf
                                <div class="small text-muted mb-2">Centang pemain yang ingin diundang. Mereka akan menerima notifikasi dan bisa memilih Terima atau Tolak.</div>
                                <input type="search" id="cari-undang" class="form-control form-control-sm mb-2" placeholder="Cari nama pemain..." autocomplete="off">
                                <div class="border rounded p-2 mb-2" style="max-height: 260px; overflow-y: auto;" id="daftar-undang">
                                    @foreach ($players as $player)
                                        <div class="form-check" data-nama="{{ mb_strtolower($player->name) }}">
                                            <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $player->id }}" id="inv-{{ $player->id }}">
                                            <label class="form-check-label" for="inv-{{ $player->id }}">
                                                {{ $player->name }}
                                                @if (in_array($player->id, $invitedIds))
                                                    <span class="badge bg-warning text-dark">Menunggu jawaban</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="btn btn-dark w-100">Kirim Undangan</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-white fw-semibold">Buat Jadwal &amp; Bagan ({{ $formatLabel }})</div>
                    <div class="card-body">
                        @if ($tournament->participants->count() < $minParticipants)
                            <p class="small text-muted mb-0">Tambahkan minimal {{ $minParticipants }} peserta dulu untuk bisa membuat jadwal.</p>
                        @else
                            <form method="POST" action="{{ route($routePrefix.'.tournaments.generate', $tournament) }}" onsubmit="return confirm('Buat jadwal &amp; bagan? Peserta tidak bisa diubah setelah ini.');">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Mulai Ronde 1</label>
                                    <input type="datetime-local" name="starts_at" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Jeda Antar Ronde</label>
                                    <select name="round_gap_minutes" class="form-select">
                                        <option value="60">1 jam</option>
                                        <option value="90">1,5 jam</option>
                                        <option value="120" selected>2 jam (umum)</option>
                                        <option value="150">2,5 jam</option>
                                        <option value="180">3 jam</option>
                                        <option value="240">4 jam</option>
                                    </select>
                                    <div class="form-text">Semua pertandingan dalam satu ronde main bersamaan di papan berbeda, seperti turnamen catur pada umumnya.</div>
                                </div>
                                <button class="btn btn-dark w-100">Buat Jadwal &amp; Bagan</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-white fw-semibold">Klasemen ({{ $tournament->participants->count() }} peserta)</div>
                <div class="table-responsive"><table class="table mb-0">
                    <thead><tr><th>#</th><th>Pemain</th><th>M</th><th>K</th><th>S</th><th>Poin</th>@if ($tournament->status === 'draft')<th></th>@endif</tr></thead>
                    <tbody>
                        @forelse ($standings as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        <x-player-avatar :user="$p->user" :size="36" />
                                        <div>
                                            <a href="{{ $routePrefix === 'admin' ? route('admin.users.show', $p->user) : route('players.show', $p->user) }}" class="fw-semibold">{{ $p->user->name }}</a>
                                            <div class="small text-muted">
                                                {{ $p->user->email }}
                                                &middot; {{ $p->user->phone ?: 'No HP belum diisi' }}
                                            </div>
                                            <x-player-meta :user="$p->user" />
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $p->wins }}</td>
                                <td>{{ $p->losses }}</td>
                                <td>{{ $p->draws }}</td>
                                <td class="fw-bold">{{ $p->points() }}</td>
                                @if ($tournament->status === 'draft')
                                    <td>
                                        <form method="POST" action="{{ route($routePrefix.'.tournaments.participants.destroy', [$tournament, $p->user]) }}" onsubmit="return confirm('Hapus peserta ini?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">&times;</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted">Belum ada peserta.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-semibold">Bagan Pertandingan</span>
                    @if ($bisaUbahBagan)
                        <span class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="collapse" data-bs-target="#edit-jadwal-bagan">Edit Jadwal</button>
                            <form method="POST" action="{{ route($routePrefix.'.tournaments.bagan.destroy', $tournament) }}" onsubmit="return confirm('Hapus bagan? Semua pertandingan akan dihapus dan turnamen kembali ke draft. Peserta tidak ikut terhapus.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus Bagan</button>
                            </form>
                        </span>
                    @endif
                </div>
                @if ($bisaUbahBagan)
                    <div class="collapse border-bottom" id="edit-jadwal-bagan">
                        <form method="POST" action="{{ route($routePrefix.'.tournaments.bagan.update', $tournament) }}" class="card-body">
                            @csrf
                            @method('PUT')
                            <div class="row g-2 mb-2">
                                <div class="col-md-7">
                                    <label class="form-label small">Mulai Ronde 1</label>
                                    <input type="datetime-local" name="starts_at" class="form-control form-control-sm" value="{{ $tournament->starts_at?->format('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small">Jeda Antar Ronde</label>
                                    <select name="round_gap_minutes" class="form-select form-select-sm">
                                        @foreach ([60 => '1 jam', 90 => '1,5 jam', 120 => '2 jam', 150 => '2,5 jam', 180 => '3 jam', 240 => '4 jam'] as $menit => $teks)
                                            <option value="{{ $menit }}" @selected((int) $tournament->round_gap_minutes === $menit)>{{ $teks }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-text mb-2">Jadwal tiap pertandingan dihitung ulang dan peserta diberi tahu. Susunan pemain tidak berubah.</div>
                            <button class="btn btn-dark btn-sm">Simpan Jadwal</button>
                        </form>
                    </div>
                @endif
                <div class="card-body">
                    <x-bracket-chart :tournament="$tournament" />
                    @if ($tournament->matches->isNotEmpty())
                        <hr>
                        <div class="small text-muted fw-semibold mb-2">Input hasil pertandingan</div>
                    @endif
                    @forelse ($tournament->matches->sortBy('round')->groupBy(fn ($m) => $m->bracket.'|'.$m->round) as $key => $matches)
                        @php
                            [$bracketKey, $round] = explode('|', $key);
                            $bracketMax = $tournament->matches->where('bracket', $bracketKey)->max('round');
                            $roundLabel = $tournament->roundLabel($bracketKey, (int) $round, (int) ($tournament->isDoubleElimination() ? $bracketMax : $tournament->matches->max('round')));
                        @endphp
                        <h6 class="text-muted mt-2 d-flex justify-content-between align-items-center">
                            <span>{{ $roundLabel }}</span>
                            @if ($matches->first()->scheduled_at)
                                <span class="small fw-normal"><i class="bi bi-clock"></i> {{ $matches->first()->scheduled_at->translatedFormat('l, d M Y H:i') }}</span>
                            @endif
                        </h6>
                        <ul class="list-group list-group-flush mb-3">
                            @foreach ($matches as $match)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            {{ $match->player1->name ?? 'Menunggu pemenang' }}
                                            vs
                                            {{ $match->player2->name ?? 'Menunggu pemenang' }}
                                        </span>

                                        @if ($match->status === 'completed')
                                            <span>
                                                <span class="badge bg-success">{{ $match->winner->name ?? 'Seri' }}</span>
                                                @if (in_array($tournament->status, ['ongoing', 'completed'], true))
                                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1" data-bs-toggle="collapse" data-bs-target="#edit-match-{{ $match->id }}">Ubah</button>
                                                @endif
                                            </span>
                                        @elseif (! $match->isReadyToPlay())
                                            <span class="badge bg-secondary">Menunggu</span>
                                        @elseif ($tournament->status === 'ongoing')
                                            @if ($match->status === 'disputed')
                                                @php
                                                    $namaLaporan = fn ($id) => $id === null ? 'belum lapor' : ($id === 0 ? 'seri' : ($id === $match->player1_id ? $match->player1->name : $match->player2->name));
                                                @endphp
                                                <span class="badge bg-warning text-dark me-1">Sengketa</span>
                                                <span class="small text-muted me-2">{{ $match->player1->name }}: {{ $namaLaporan($match->player1_reported_winner_id) }} &middot; {{ $match->player2->name }}: {{ $namaLaporan($match->player2_reported_winner_id) }}</span>
                                            @endif
                                            <form method="POST" action="{{ route($routePrefix.'.tournaments.matches.result', [$tournament, $match]) }}" class="d-flex gap-1">
                                                @csrf
                                                <select name="winner_id" class="form-select form-select-sm" required>
                                                    <option value="">Hasil...</option>
                                                    <option value="{{ $match->player1_id }}">{{ $match->player1->name }} menang</option>
                                                    <option value="{{ $match->player2_id }}">{{ $match->player2->name }} menang</option>
                                                    @if ($tournament->isRoundRobin())
                                                        <option value="0">Seri</option>
                                                    @endif
                                                </select>
                                                <button class="btn btn-sm btn-dark">Simpan</button>
                                            </form>
                                        @endif
                                    </div>

                                    @if ($match->status === 'completed')
                                        <div class="collapse mt-2" id="edit-match-{{ $match->id }}">
                                            @if ($match->next_match_id && $match->nextMatch?->status === 'completed')
                                                <p class="small text-muted mb-0">Tidak bisa diubah, pemenang sudah bertanding di babak berikutnya.</p>
                                            @else
                                                <form method="POST" action="{{ route($routePrefix.'.tournaments.matches.result', [$tournament, $match]) }}" class="d-flex gap-1">
                                                    @csrf
                                                    <select name="winner_id" class="form-select form-select-sm" required>
                                                        <option value="{{ $match->player1_id }}" @selected($match->winner_id === $match->player1_id)>{{ $match->player1->name }} menang</option>
                                                        <option value="{{ $match->player2_id }}" @selected($match->winner_id === $match->player2_id)>{{ $match->player2->name }} menang</option>
                                                        @if ($tournament->isRoundRobin())
                                                            <option value="0" @selected($match->winner_id === null)>Seri</option>
                                                        @endif
                                                    </select>
                                                    <button class="btn btn-sm btn-outline-dark">Perbarui</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @empty
                        <p class="text-muted mb-0">Jadwal &amp; bagan belum dibuat. Tambahkan minimal {{ $minParticipants }} peserta lalu isi form "Buat Jadwal &amp; Bagan" di sebelah kiri.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const cari = document.getElementById('cari-undang');
    if (!cari) return;
    const items = document.querySelectorAll('#daftar-undang [data-nama]');

    cari.addEventListener('input', function () {
        const q = cari.value.trim().toLowerCase();
        items.forEach(function (item) {
            item.classList.toggle('d-none', q !== '' && !item.dataset.nama.includes(q));
        });
    });
})();
</script>
@endpush
