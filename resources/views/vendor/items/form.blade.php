@extends('layouts.app')

@section('title', $item->exists ? 'Edit Barang' : 'Tambah Barang')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">{{ $item->exists ? 'Edit Barang' : 'Tambah Barang' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $item->exists ? route('vendor.items.update', $item) : route('vendor.items.store') }}">
                        @csrf
                        @if ($item->exists) @method('PUT') @endif

                        <div class="mb-3">
                            <label class="form-label" for="f1-name">Nama Barang</label>
                            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f2-description">Deskripsi</label>
                            <textarea id="f2-description" name="description" class="form-control" rows="3" maxlength="1000">{{ old('description', $item->description) }}</textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label" for="f3-point-cost">Nilai Tukar (poin)</label>
                                <input id="f3-point-cost" type="number" name="point_cost" class="form-control" min="1" max="1000000" value="{{ old('point_cost', $item->point_cost) }}" required>
                                <div class="form-text">Ditentukan oleh vendor.</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="f4-stock">Stok</label>
                                <input id="f4-stock" type="number" name="stock" class="form-control" min="0" max="100000" value="{{ old('stock', $item->stock) }}" placeholder="Kosongkan = tanpa batas">
                            </div>
                        </div>
                        <div class="form-check mb-4">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $item->is_active))>
                            <label class="form-check-label" for="is_active">Tampilkan di menu tukar poin</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-dark flex-fill">Simpan</button>
                            <a href="{{ route('vendor.items.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
