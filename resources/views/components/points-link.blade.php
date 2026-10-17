@props(['user', 'class' => ''])

@php
    $viewer = auth()->user();
    $href = null;

    if ($viewer && ! $user->isAdmin()) {
        if ($viewer->isAdmin()) {
            $href = route('admin.users.activity', $user);
        } elseif ($viewer->id === $user->id) {
            $href = route('points.history');
        }
    }
@endphp

@if ($href)
    <a href="{{ $href }}" class="{{ $class }} text-decoration-none" title="Lihat riwayat poin">{{ number_format($user->points) }}</a>
@else
    <span class="{{ $class }}">{{ number_format($user->points) }}</span>
@endif
