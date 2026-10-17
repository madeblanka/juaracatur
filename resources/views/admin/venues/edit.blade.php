@extends('layouts.app')

@section('title', 'Edit Tempat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Edit Tempat Bertanding</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.venues.update', $venue) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="f1-name">Nama Tempat</label>
                            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name', $venue->name) }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f2-address">Alamat</label>
                            <textarea id="f2-address" name="address" class="form-control" rows="2">{{ old('address', $venue->address) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f3-wilayah">Wilayah</label>
                            <input id="f3-wilayah" type="text" name="wilayah" class="form-control" value="{{ old('wilayah', $venue->wilayah) }}" placeholder="Contoh: Denpasar, Bali">
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $venue->is_active))>
                            <label class="form-check-label" for="is_active">Aktif (bisa dipilih pemain)</label>
                        </div>
                        <button class="btn btn-dark w-100">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
