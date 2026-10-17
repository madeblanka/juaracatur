<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: radial-gradient(circle at 50% 0%, #2b0505 0%, #0d0d0d 55%, #000 100%);
        }
        .auth-card { max-width: 440px; width: 100%; margin: 0 auto; border-top: 3px solid #e0463a; }
        .auth-card-wide { max-width: 760px; }
        .brand-word {
            font-family: Georgia, 'Times New Roman', serif;
            font-weight: 800;
            font-style: italic;
            letter-spacing: 1.5px;
            font-size: 1.6rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card auth-card @yield('auth_card_class') shadow">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <x-logo :size="64" />
                    <h4 class="brand-word mb-0 mt-2">JUARACATUR</h4>
                    <p class="text-muted small">Arena duel catur & turnamen</p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
