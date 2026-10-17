@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-4 text-center">
                    <div class="fs-1 mb-2"><i class="bi bi-envelope-check"></i></div>
                    <h5 class="fw-bold">Verifikasi email kamu</h5>
                    <p class="text-muted">Kamu perlu memverifikasi email sebelum bisa menantang pemain, menerima undangan, atau bertanding.</p>
                    <p class="small mb-4">Kami mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>. Klik tautan itu di email kamu, lalu kembali ke sini.</p>

                    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                        @csrf
                        <button class="btn btn-dark w-100">Kirim Ulang Email Verifikasi</button>
                    </form>
                    <p class="small text-muted mb-0">Tidak menerima email? Cek folder spam atau promosi.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
