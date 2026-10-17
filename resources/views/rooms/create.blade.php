@extends('layouts.app')

@section('title', 'Buat Tantangan')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Tantang Pemain di Sekitarmu</div>
                <div class="card-body">
                    <div class="alert alert-warning small"><i class="bi bi-exclamation-triangle"></i> Penantang wajib membawa papan catur sendiri saat bertanding.</div>

                    <div class="d-flex gap-2 mb-3">
                        <a href="{{ route('players.index') }}" class="btn btn-sm btn-outline-dark">🔍 Cari Lawan</a>
                        <a href="{{ route('venues.index') }}" class="btn btn-sm btn-outline-dark">🔍 Cari Tempat</a>
                    </div>

                    @if (auth()->user()->points < 1)
                        <div class="alert alert-danger small mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Poinmu tidak cukup (0 poin) untuk membuat tantangan baru. Mungkin poinmu sedang ditahan di room lain yang masih berlangsung — selesaikan dulu room tersebut.
                        </div>
                    @else
                    <form method="POST" action="{{ route('rooms.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="f1-opponent-id">Lawan</label>
                            <select id="f1-opponent-id" name="opponent_id" class="form-select">
                                <option value="">Terbuka untuk siapapun</option>
                                @foreach ($players as $player)
                                    <option value="{{ $player->id }}" @selected(old('opponent_id', request('opponent_id')) == $player->id)>{{ $player->name }} ({{ $player->points }} poin){{ $player->wilayah ? ' - '.$player->wilayah : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="f2-venue-id">Tempat Bertanding</label>
                            <select id="f2-venue-id" name="venue_id" class="form-select" required>
                                <option value="">Pilih tempat...</option>
                                @foreach ($venues as $venue)
                                    <option value="{{ $venue->id }}" @selected(old('venue_id', request('venue_id')) == $venue->id)>{{ $venue->name }}{{ $venue->wilayah ? ' - '.$venue->wilayah : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="f3-wager-points">Taruhan Poin (hadiah untuk pemenang)</label>
                            <input id="f3-wager-points" type="number" name="wager_points" class="form-control" min="1" max="{{ auth()->user()->points }}" value="{{ old('wager_points', 500) }}" required>
                            <div class="form-text">Poin kamu saat ini: {{ number_format(auth()->user()->points) }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="f4-scheduled-at">Jadwal (opsional)</label>
                            <input id="f4-scheduled-at" type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="f5-notes">Catatan (opsional)</label>
                            <textarea id="f5-notes" name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Buat Tantangan</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
