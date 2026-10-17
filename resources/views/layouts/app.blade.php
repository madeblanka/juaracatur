<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f5f7; }
        .navbar-brand { font-weight: 700; letter-spacing: .5px; }
        .brand-word {
            font-family: Georgia, 'Times New Roman', serif;
            font-weight: 800;
            font-style: italic;
            letter-spacing: 1.5px;
            font-size: 1.3rem;
            color: #fff;
            text-shadow: 0 0 10px rgba(224,70,58,.55);
        }
        .points-badge { font-size: .95rem; }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .min-w-0 { min-width: 0; }
        .text-break { overflow-wrap: anywhere; }
        @media (max-width: 991px) {
            .navbar-collapse { padding: .5rem 0 .75rem; }
            .navbar-nav .nav-link { padding: .6rem 0; }
        }
        @media (max-width: 576px) {
            .container { padding-left: 16px; padding-right: 16px; }
            h4 { font-size: 1.3rem; }
            .btn { min-height: 38px; }
            .btn-sm { min-height: 34px; }
            .table > :not(caption) > * > * { padding: .5rem .5rem; }
            .alert { font-size: .95rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('layouts.partials.navbar')

    <div class="container py-4">
        @if (auth()->user()?->role === 'player' && ! auth()->user()->hasVerifiedEmail())
            <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span><i class="bi bi-envelope-exclamation"></i> Email kamu belum diverifikasi. Verifikasi dulu untuk bisa menantang atau bertanding.</span>
                <a href="{{ route('verification.notice') }}" class="btn btn-sm btn-dark">Verifikasi Sekarang</a>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('layouts.partials.notifications-poll')
    @stack('scripts')
</body>
</html>
