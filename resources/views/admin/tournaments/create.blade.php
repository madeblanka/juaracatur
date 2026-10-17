@extends('layouts.app')

@section('title', 'Buat Turnamen')

@section('content')
    @php $routePrefix = explode('.', request()->route()->getName())[0]; @endphp
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Buat Turnamen Baru</div>
                <div class="card-body">
                    <form method="POST" action="{{ route($routePrefix.'.tournaments.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="f1-name">Nama Turnamen</label>
                            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f2-description">Deskripsi</label>
                            <textarea id="f2-description" name="description" class="form-control" rows="5" placeholder="Contoh:&#10;Hadiah: Rp 500.000&#10;Biaya daftar: gratis&#10;Aturan: 10 menit per pemain">{{ old('description') }}</textarea>
                            <div class="form-text">Tulis per baris. Baris baru akan tampil apa adanya di halaman turnamen.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f3-flyer">Flyer <span class="text-muted">(opsional)</span></label>
                            <input id="f3-flyer" type="file" name="flyer" class="form-control" accept="image/*">
                            <div class="form-text">Gambar jpg, png, atau webp, maksimal 2MB. Ditampilkan di daftar dan detail turnamen.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f4-venue-id">Tempat (opsional)</label>
                            <select id="f4-venue-id" name="venue_id" class="form-select">
                                <option value="">-</option>
                                @foreach ($venues as $venue)
                                    <option value="{{ $venue->id }}" @selected(old('venue_id') == $venue->id)>{{ $venue->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="input-biaya-buat">Biaya daftar (poin)</label>
                            <input id="input-biaya-buat" type="number" name="entry_points" class="form-control" min="0" max="100000" value="{{ old('entry_points', 0) }}" required>
                            <div class="form-text">Isi 0 jika gratis. Pemain membayar dengan poin saat mendaftar.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Format Turnamen</label>
                            <div class="form-check">
                                <input class="form-check-input format-radio" type="radio" name="format" id="format_rr" value="round_robin" @checked(old('format', 'round_robin') === 'round_robin')>
                                <label class="form-check-label" for="format_rr">
                                    <strong>Round Robin</strong> — semua peserta saling bertanding, juara ditentukan dari total kemenangan.
                                </label>
                            </div>
                            <div id="rr-rounds-wrap" class="ms-4 mb-2" style="display:none;">
                                <label class="form-label small" for="input-rr-rounds">Jumlah Putaran</label>
                                <select id="input-rr-rounds" name="rr_rounds" class="form-select form-select-sm" style="max-width: 320px;">
                                    <option value="1" @selected((int) old('rr_rounds', 1) === 1)>1x — Single Round Robin (setiap pasangan main 1x)</option>
                                    <option value="2" @selected((int) old('rr_rounds') === 2)>2x — Double Round Robin (setiap pasangan main 2x)</option>
                                    <option value="3" @selected((int) old('rr_rounds') === 3)>3x — Triple Round Robin (setiap pasangan main 3x)</option>
                                    <option value="4" @selected((int) old('rr_rounds') === 4)>4x — Quadruple Round Robin (setiap pasangan main 4x)</option>
                                    <option value="5" @selected((int) old('rr_rounds') === 5)>5x — Quintuple Round Robin (setiap pasangan main 5x)</option>
                                </select>
                                <div class="form-text">Semakin banyak putaran, semakin banyak pertandingan dan semakin lama turnamen berlangsung.</div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input format-radio" type="radio" name="format" id="format_se" value="single_elimination" @checked(old('format') === 'single_elimination')>
                                <label class="form-check-label" for="format_se">
                                    <strong>Single Elimination</strong> — kalah 1x langsung gugur, cocok untuk peserta banyak.
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input format-radio" type="radio" name="format" id="format_de" value="double_elimination" @checked(old('format') === 'double_elimination')>
                                <label class="form-check-label" for="format_de">
                                    <strong>Bracket dengan Lower Bracket</strong> — kalah 1x turun ke lower bracket, kalah 2x baru gugur. Jumlah peserta harus 4, 8, 16, atau 32.
                                </label>
                            </div>
                        </div>
                        <button class="btn btn-dark w-100">Buat Turnamen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const radios = document.querySelectorAll('.format-radio');
        const wrap = document.getElementById('rr-rounds-wrap');

        function sync() {
            const checked = document.querySelector('.format-radio:checked');
            wrap.style.display = checked && checked.value === 'round_robin' ? '' : 'none';
        }

        radios.forEach(r => r.addEventListener('change', sync));
        sync();
    })();
    </script>
    @endpush
@endsection
