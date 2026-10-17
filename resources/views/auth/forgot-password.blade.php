@extends('layouts.guest')

@section('title', 'Lupa Password')

@section('content')
    <p class="text-muted small mb-3">Masukkan email akunmu. Kami akan kirim link untuk membuat password baru.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="f1-email">Email</label>
            <input id="f1-email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <button type="submit" class="btn btn-dark w-100">Kirim Link Reset Password</button>
    </form>
    <p class="text-center mt-3 mb-0 small"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
@endsection
