<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoPlayersSeeder extends Seeder
{
    private const NAMES = [
        'Ahmad', 'Muhammad', 'Made', 'Putu', 'Wayan', 'Nyoman', 'Ketut', 'Putra', 'Budi', 'Agus',
        'Dewi', 'Siti', 'Andi', 'Rudi', 'Joko', 'Eko', 'Fajar', 'Indra', 'Rizki', 'Dedi',
        'Hendra', 'Yanto', 'Bambang', 'Slamet', 'Wahyu', 'Hidayat', 'Fauzi', 'Ridwan', 'Taufik', 'Iwan',
        'Doni', 'Yusuf', 'Ali', 'Hasan', 'Umar', 'Abdul', 'Bagus', 'Komang', 'Kadek', 'Gede',
        'Nengah', 'Ayu', 'Sri', 'Ratna', 'Yuni', 'Rina', 'Lina', 'Dian', 'Maya', 'Fitri',
        'Indah', 'Wulan', 'Sari', 'Ani', 'Nia', 'Tuti', 'Wati', 'Ida', 'Yanti', 'Santi',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('bangsat2310!');
        $total = count(self::NAMES);

        for ($i = 1; $i <= 100; $i++) {
            $number = str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            User::updateOrCreate(
                ['email' => "player{$number}@player.juaracatur.test"],
                [
                    'name' => self::NAMES[($i - 1) % $total],
                    'password' => $password,
                    'role' => 'player',
                ]
            );
        }
    }
}
