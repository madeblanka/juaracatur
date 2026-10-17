@extends('layouts.app')

@section('title', 'Edit Pemain')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Edit Data Pemain</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $player) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="f1-name">Nama</label>
                            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name', $player->name) }}" maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f2-email">Email</label>
                            <input id="f2-email" type="email" name="email" class="form-control" value="{{ old('email', $player->email) }}" maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f3-phone">Nomor HP</label>
                            <input id="f3-phone" type="text" name="phone" class="form-control" value="{{ old('phone', $player->phone) }}" maxlength="20">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f4-birthdate">Tanggal Lahir</label>
                            <input id="f4-birthdate" type="date" name="birthdate" class="form-control" value="{{ old('birthdate', $player->birthdate?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f5-role">Role</label>
                            <select id="f5-role" name="role" class="form-select" required>
                                <option value="player" @selected(old('role', $player->role) === 'player')>Pemain</option>
                                <option value="vendor" @selected(old('role', $player->role) === 'vendor')>Vendor (bisa mengelola barang tukar poin)</option>
                            </select>
                        </div>
                        <div class="form-check mb-4">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $player->is_active))>
                            <label class="form-check-label" for="is_active">Akun aktif (jika nonaktif, pemain tidak bisa login)</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-dark flex-fill">Simpan</button>
                            <a href="{{ route('admin.users.show', $player) }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
