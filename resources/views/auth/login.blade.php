@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="f1-email">Email</label>
            <input id="f1-email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f2-password">Password</label>
            <input id="f2-password" type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check mb-3 d-flex justify-content-between align-items-center">
            <div>
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <a href="{{ route('password.request') }}" class="small">Lupa password?</a>
        </div>
        <button type="submit" class="btn btn-dark w-100">Masuk</button>
    </form>
    <div class="text-center small mt-3">
        Punya tempat bertanding? <a href="{{ route('venue.register') }}">Daftar sebagai venue</a>
    </div>
    <p class="text-center mt-3 mb-0 small">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
@endsection
