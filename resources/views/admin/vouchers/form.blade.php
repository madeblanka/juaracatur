@extends('layouts.app')

@section('title', $voucher->exists ? 'Edit Voucher' : 'Tambah Voucher')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">{{ $voucher->exists ? 'Edit Voucher' : 'Tambah Voucher' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $voucher->exists ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}">
                        @csrf
                        @if ($voucher->exists) @method('PUT') @endif

                        <div class="mb-3">
                            <label class="form-label" for="f1-name">Nama Voucher</label>
                            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name', $voucher->name) }}" maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f2-description">Deskripsi / Nilai</label>
                            <textarea id="f2-description" name="description" class="form-control" rows="2" maxlength="1000" placeholder="Contoh: Diskon 20% di toko X">{{ old('description', $voucher->description) }}</textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label" for="f3-point-cost">Harga Tukar (poin)</label>
                                <input id="f3-point-cost" type="number" name="point_cost" class="form-control" min="1" max="1000000" value="{{ old('point_cost', $voucher->point_cost) }}" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label" for="f4-stock">Kuota</label>
                                <input id="f4-stock" type="number" name="stock" class="form-control" min="0" max="100000" value="{{ old('stock', $voucher->stock) }}" placeholder="Kosongkan jika tak terbatas">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f5-expires-at">Berlaku sampai (opsional)</label>
                            <input id="f5-expires-at" type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $voucher->expires_at?->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="form-check mb-4">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $voucher->is_active))>
                            <label class="form-check-label" for="is_active">Tampilkan di menu tukar poin</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-dark flex-fill">Simpan</button>
                            <a href="{{ route('admin.vouchers.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
