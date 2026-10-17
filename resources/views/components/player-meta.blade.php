@props(['user', 'class' => 'small text-muted'])

<div class="{{ $class }}">
    <div><i class="bi bi-geo-alt"></i> {{ $user->domisiliLabel() ?: 'Alamat belum diisi' }}</div>
    <div><i class="bi bi-calendar3"></i> {{ $user->usia() !== null ? $user->usia().' tahun' : 'Usia belum diisi' }}</div>
</div>
