<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $handle = gzopen(database_path('data/wilayah.csv.gz'), 'rb');
        gzgets($handle);

        $batch = [];

        while (($line = gzgets($handle)) !== false) {
            [$kode, $nama] = explode("\t", rtrim($line, "\r\n"), 2);

            $batch[] = [
                'kode' => $kode,
                'nama' => $nama,
                'parent_kode' => strrpos($kode, '.') === false ? null : substr($kode, 0, strrpos($kode, '.')),
            ];

            if (count($batch) === 1000) {
                DB::table('wilayah')->upsert($batch, ['kode'], ['nama', 'parent_kode']);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('wilayah')->upsert($batch, ['kode'], ['nama', 'parent_kode']);
        }

        gzclose($handle);
    }
}
