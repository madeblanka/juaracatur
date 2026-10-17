<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@juaracatur.test'],
            [
                'name' => 'Admin Juaracatur',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'points' => 0,
            ]
        );

        collect([
            ['name' => 'Taman Kota Chess Corner', 'address' => 'Taman Kota, Jl. Merdeka No. 1'],
            ['name' => 'Kafe Catur Denpasar', 'address' => 'Jl. Gatot Subroto No. 45, Denpasar'],
            ['name' => 'GOR Catur Sanur', 'address' => 'Jl. Danau Tamblingan, Sanur'],
        ])->each(fn (array $venue) => Venue::firstOrCreate(['name' => $venue['name']], $venue));
    }
}
