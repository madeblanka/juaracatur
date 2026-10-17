@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label class="form-label" for="f1-email">Email</label>
            <input id="f1-email" type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f2-password">Password Baru</label>
            <input id="f2-password" type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f3-password-confirmation">Konfirmasi Password Baru</label>
            <input id="f3-password-confirmation" type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Simpan Password Baru</button>
    </form>
@endsection
