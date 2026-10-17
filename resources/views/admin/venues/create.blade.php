@extends('layouts.app')

@section('title', 'Tambah Tempat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Tambah Tempat Bertanding</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.venues.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="f1-name">Nama Tempat</label>
                            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f2-address">Alamat</label>
                            <textarea id="f2-address" name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f3-wilayah">Wilayah</label>
                            <input id="f3-wilayah" type="text" name="wilayah" class="form-control" value="{{ old('wilayah') }}" placeholder="Contoh: Denpasar, Bali">
                            <div class="form-text">Dipakai untuk pencarian tempat berdasarkan wilayah.</div>
                        </div>
                        <button class="btn btn-dark w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
