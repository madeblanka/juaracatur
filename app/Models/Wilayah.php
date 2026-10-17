<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public static function labelDomisili(string $kelurahanKode): string
    {
        return static::namaUntukKode($kelurahanKode)
            ->map(fn (?string $nama) => $nama)
            ->filter()
            ->implode(', ');
    }

    public static function kabupatenNama(string $kelurahanKode): ?string
    {
        return static::find(substr($kelurahanKode, 0, 5))?->nama;
    }

    /** @return Collection<int, string|null> */
    private static function namaUntukKode(string $kelurahanKode): Collection
    {
        $kodes = [
            $kelurahanKode,
            substr($kelurahanKode, 0, 8),
            substr($kelurahanKode, 0, 5),
        ];

        $namaByKode = static::whereIn('kode', $kodes)->pluck('nama', 'kode');

        return collect($kodes)->map(fn (string $kode) => $namaByKode[$kode] ?? null);
    }
}
