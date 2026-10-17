@props(['user', 'size' => 44])

@if ($user->photoUrl())
    <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}" class="rounded-circle flex-shrink-0" width="{{ $size }}" height="{{ $size }}" style="object-fit: cover;" loading="lazy">
@else
    <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: {{ $size }}px; height: {{ $size }}px;">
        <i class="bi bi-person-fill" style="font-size: {{ round($size * 0.5) }}px;"></i>
    </div>
@endif
