@extends('layouts.app')

@section('title', 'Room '.$room->code)

@section('content')
    @php
        $me = auth()->user();
        $isChallenger = $room->challenger_id === $me->id;
        $isOpponent = $room->opponent_id === $me->id;
        $isParticipant = $isChallenger || $isOpponent;
        $canAccept = $room->status === 'open' && ! $isChallenger && ($room->opponent_id === null || $isOpponent);
        $myReport = $isChallenger ? $room->challenger_reported_winner_id : ($isOpponent ? $room->opponent_reported_winner_id : null);
    @endphp

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Room {{ $room->code }}</h5>
                            <span class="badge bg-{{ match($room->status) { 'open' => 'secondary', 'accepted' => 'primary', 'completed' => 'success', 'disputed' => 'danger', default => 'dark' } }}">{{ strtoupper($room->status) }}</span>
                        </div>
                        <span class="badge bg-success fs-6"><i class="bi bi-coin"></i> {{ $room->wager_points }} poin</span>
                    </div>

                    @if (in_array($room->status, ['accepted', 'disputed']))
                        <div class="small text-muted mb-3"><i class="bi bi-lock"></i> Poin taruhan kedua pemain sedang <strong>ditahan</strong> di room ini sampai hasil dikonfirmasi.</div>
                    @endif

                    <div class="row mb-3 text-center">
                        <div class="col-5">
                            <div class="fw-bold">{{ $room->challenger->name }}</div>
                            <div class="small text-muted">Penantang</div>
                            @if ($isParticipant && $room->opponent_id !== null && $room->challenger->whatsappUrl())
                                <a href="{{ $room->challenger->whatsappUrl() }}" target="_blank" rel="noopener" class="small text-success text-decoration-none d-inline-block mt-1">
                                    <i class="bi bi-whatsapp"></i> {{ $room->challenger->phone }}
                                </a>
                            @endif
                        </div>
                        <div class="col-2 text-muted">VS</div>
                        <div class="col-5">
                            <div class="fw-bold">{{ $room->opponent->name ?? 'Menunggu lawan' }}</div>
                            <div class="small text-muted">Lawan</div>
                            @if ($isParticipant && $room->opponent && $room->opponent->whatsappUrl())
                                <a href="{{ $room->opponent->whatsappUrl() }}" target="_blank" rel="noopener" class="small text-success text-decoration-none d-inline-block mt-1">
                                    <i class="bi bi-whatsapp"></i> {{ $room->opponent->phone }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Tempat Bertanding</span>
                            <span class="fw-semibold">{{ $room->venue->name }}</span>
                        </li>
                        @if ($room->scheduled_at)
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Jadwal</span>
                                <span class="fw-semibold">{{ $room->scheduled_at->translatedFormat('d M Y H:i') }}</span>
                            </li>
                        @endif
                        @if ($room->notes)
                            <li class="list-group-item px-0">
                                <span class="text-muted d-block">Catatan</span>
                                {{ $room->notes }}
                            </li>
                        @endif
                        @if ($room->winner)
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Pemenang</span>
                                <span class="fw-bold text-success">{{ $room->winner->name }}</span>
                            </li>
                        @endif
                    </ul>

                    <div class="alert alert-warning small mb-3"><i class="bi bi-exclamation-triangle"></i> Penantang wajib membawa papan catur sendiri saat bertanding di lokasi.</div>

                    @if ($room->status === 'accepted' && $isParticipant)
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-semibold mb-0"><i class="bi bi-stopwatch"></i> Timer Pertandingan</h6>
                                <div>
                                    <button type="button" id="timer-start" class="btn btn-sm btn-success">Mulai</button>
                                    <button type="button" id="timer-switch" class="btn btn-sm btn-outline-dark" disabled>Ganti Giliran</button>
                                    <button type="button" id="timer-reset" class="btn btn-sm btn-outline-danger">Reset</button>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="small text-muted">{{ $room->challenger->name }}</div>
                                    <div class="fs-3 fw-bold font-monospace" id="timer-challenger">00:00</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">{{ $room->opponent->name }}</div>
                                    <div class="fs-3 fw-bold font-monospace" id="timer-opponent">00:00</div>
                                </div>
                            </div>
                            <div class="form-text mt-2">Timer informal di browser, tidak tersimpan — cuma bantu memantau waktu selama di luar jam catur resmi.</div>
                        </div>
                    @endif

                    @if ($canAccept)
                        <form method="POST" action="{{ route('rooms.accept', $room) }}" class="mb-2">
                            @csrf
                            <button class="btn btn-success w-100">Terima Tantangan</button>
                        </form>
                    @endif

                    @if ($isChallenger && $room->status === 'open')
                        <form method="POST" action="{{ route('rooms.cancel', $room) }}" class="mb-2">
                            @csrf
                            <button class="btn btn-outline-danger w-100">Batalkan Room</button>
                        </form>

                        @if ($room->opponent_id === null)
                            <form method="POST" action="{{ route('rooms.invite', $room) }}" class="d-flex gap-2">
                                @csrf
                                <select name="user_id" class="form-select" required>
                                    <option value="">Undang pemain tertentu...</option>
                                    @foreach ($invitablePlayers as $player)
                                        <option value="{{ $player->id }}">{{ $player->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-dark text-nowrap">Undang</button>
                            </form>
                            <div class="form-text">Mengundang tidak mengunci room — pemain lain tetap bisa menerima tantangan ini duluan.</div>
                        @endif
                    @endif

                    @if ($room->status === 'accepted' && $isParticipant)
                        @php
                            $myId = $me->id;
                            $opponentOfMeId = $isChallenger ? $room->opponent_id : $room->challenger_id;
                        @endphp
                        <hr>
                        <h6 class="fw-semibold">Laporkan Hasil Pertandingan</h6>
                        @if ($myReport)
                            <p class="small text-muted">Kamu sudah melaporkan pemenang: <strong>{{ $myReport === $room->challenger_id ? $room->challenger->name : $room->opponent->name }}</strong>. Menunggu konfirmasi lawan.</p>
                        @else
                            <p class="small text-muted mb-2">Pilih hasil sejujurnya. Hasil baru final kalau laporanmu dan lawan sama.</p>
                            <div class="d-flex gap-2">
                                <form method="POST" action="{{ route('rooms.report', $room) }}" class="flex-fill" onsubmit="return confirm('Apakah kamu yakin MENANG di pertandingan ini?');">
                                    @csrf
                                    <input type="hidden" name="winner_id" value="{{ $myId }}">
                                    <button class="btn btn-success w-100">✅ Saya Menang</button>
                                </form>
                                <form method="POST" action="{{ route('rooms.report', $room) }}" class="flex-fill" onsubmit="return confirm('Apakah kamu yakin KALAH di pertandingan ini?');">
                                    @csrf
                                    <input type="hidden" name="winner_id" value="{{ $opponentOfMeId }}">
                                    <button class="btn btn-outline-danger w-100">❌ Saya Kalah</button>
                                </form>
                            </div>
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle"></i> Catatan: kalau laporan kamu dan lawan berbeda, poin taruhan akan <strong>ditahan sementara (status sengketa)</strong> sampai admin memastikan siapa pemenang yang sebenarnya. Jangan asal klik.
                            </div>
                        @endif
                    @endif

                    @if ($room->status === 'disputed')
                        <div class="alert alert-danger small mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Laporan hasil kamu dan lawan berbeda. <strong>Poin taruhan ditahan sementara</strong> sampai admin meninjau dan menentukan pemenang sebenarnya.
                        </div>
                    @endif
                </div>
            </div>

            @if ($room->pointTransactions->isNotEmpty())
                <div class="card">
                    <div class="card-header bg-white fw-semibold">Riwayat Poin Room Ini</div>
                    <div class="list-group list-group-flush">
                        @foreach ($room->pointTransactions as $tx)
                            <div class="list-group-item d-flex justify-content-between">
                                <span>{{ $tx->user->name }}</span>
                                <span class="fw-semibold {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ $tx->amount }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($isParticipant && $room->opponent_id !== null)
                <div class="card">
                    <div class="card-header bg-white fw-semibold">Chat Room</div>
                    <div class="card-body">
                        <div id="chat-messages" class="mb-3" style="max-height: 280px; overflow-y: auto;" data-after="0">
                            <p class="text-muted small text-center mb-0" id="chat-empty">Belum ada pesan. Mulai obrolan!</p>
                        </div>
                        <form id="chat-form" class="d-flex gap-2">
                            @csrf
                            <input type="text" id="chat-input" class="form-control" maxlength="1000" placeholder="Tulis pesan..." autocomplete="off" required>
                            <button class="btn btn-dark">Kirim</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    @if ($room->status === 'accepted' && $isParticipant)
        <script>
        (function () {
            let activeSide = 'challenger';
            let running = false;
            let seconds = { challenger: 0, opponent: 0 };
            let intervalId = null;

            const elChallenger = document.getElementById('timer-challenger');
            const elOpponent = document.getElementById('timer-opponent');
            const btnStart = document.getElementById('timer-start');
            const btnSwitch = document.getElementById('timer-switch');
            const btnReset = document.getElementById('timer-reset');

            function format(s) {
                const m = Math.floor(s / 60).toString().padStart(2, '0');
                const r = (s % 60).toString().padStart(2, '0');
                return m + ':' + r;
            }

            function render() {
                elChallenger.textContent = format(seconds.challenger);
                elOpponent.textContent = format(seconds.opponent);
                elChallenger.classList.toggle('text-success', running && activeSide === 'challenger');
                elOpponent.classList.toggle('text-success', running && activeSide === 'opponent');
            }

            btnStart.addEventListener('click', function () {
                if (!running) {
                    running = true;
                    intervalId = setInterval(function () { seconds[activeSide]++; render(); }, 1000);
                    btnStart.textContent = 'Jeda';
                    btnStart.classList.remove('btn-success');
                    btnStart.classList.add('btn-warning');
                    btnSwitch.disabled = false;
                } else {
                    running = false;
                    clearInterval(intervalId);
                    btnStart.textContent = 'Lanjutkan';
                    btnStart.classList.remove('btn-warning');
                    btnStart.classList.add('btn-success');
                }
                render();
            });

            btnSwitch.addEventListener('click', function () {
                activeSide = activeSide === 'challenger' ? 'opponent' : 'challenger';
                render();
            });

            btnReset.addEventListener('click', function () {
                running = false;
                clearInterval(intervalId);
                seconds = { challenger: 0, opponent: 0 };
                activeSide = 'challenger';
                btnStart.textContent = 'Mulai';
                btnStart.classList.remove('btn-warning');
                btnStart.classList.add('btn-success');
                btnSwitch.disabled = true;
                render();
            });

            render();
        })();
        </script>
    @endif

    @if ($isParticipant && $room->opponent_id !== null)
        <script>
        (function () {
            const messagesEl = document.getElementById('chat-messages');
            const emptyEl = document.getElementById('chat-empty');
            const form = document.getElementById('chat-form');
            const input = document.getElementById('chat-input');
            const myUserId = {{ $me->id }};
            const messagesUrl = '{{ route('rooms.messages.index', $room) }}';
            const sendUrl = '{{ route('rooms.messages.store', $room) }}';
            const csrfToken = document.querySelector('meta[name=csrf-token]').content;

            function appendMessage(msg) {
                if (emptyEl) emptyEl.remove();
                const mine = msg.user_id === myUserId;
                const wrap = document.createElement('div');
                wrap.className = 'mb-2 ' + (mine ? 'text-end' : 'text-start');
                wrap.innerHTML = '<div class="small text-muted">' + (mine ? 'Kamu' : escapeHtml(msg.user_name)) + ' &middot; ' + msg.time + '</div>' +
                    '<div class="d-inline-block px-2 py-1 rounded ' + (mine ? 'bg-dark text-white' : 'bg-light') + '">' + escapeHtml(msg.message) + '</div>';
                messagesEl.appendChild(wrap);
                messagesEl.scrollTop = messagesEl.scrollHeight;
                messagesEl.dataset.after = msg.id;
            }

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            async function poll() {
                try {
                    const after = messagesEl.dataset.after || 0;
                    const res = await fetch(messagesUrl + '?after=' + after, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    data.forEach(appendMessage);
                } catch (e) { /* ignore transient network errors */ }
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const message = input.value.trim();
                if (!message) return;
                input.value = '';
                try {
                    const res = await fetch(sendUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ message }),
                    });
                    if (res.ok) {
                        appendMessage(await res.json());
                    }
                } catch (e) { /* ignore */ }
            });

            poll();
            setInterval(poll, 3000);
        })();
        </script>
    @endif
@endpush
