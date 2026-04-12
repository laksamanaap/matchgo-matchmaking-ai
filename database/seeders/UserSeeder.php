<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Admin MatchGo',
            'email'    => 'admin@matchgo.id',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Players
        $players = [
            ['name' => 'Budi Santoso',     'email' => 'budi@matchgo.id'],
            ['name' => 'Rizky Pratama',    'email' => 'rizky@matchgo.id'],
            ['name' => 'Andi Kurniawan',   'email' => 'andi@matchgo.id'],
            ['name' => 'Dimas Prasetyo',   'email' => 'dimas@matchgo.id'],
            ['name' => 'Fajar Nugroho',    'email' => 'fajar@matchgo.id'],
            ['name' => 'Galih Wicaksono',  'email' => 'galih@matchgo.id'],
            ['name' => 'Hendra Gunawan',   'email' => 'hendra@matchgo.id'],
            ['name' => 'Irfan Hakim',      'email' => 'irfan@matchgo.id'],
            ['name' => 'Joko Widodo',      'email' => 'joko@matchgo.id'],
            ['name' => 'Kevin Sanjaya',    'email' => 'kevin@matchgo.id'],
            ['name' => 'Luthfi Rahman',    'email' => 'luthfi@matchgo.id'],
            ['name' => 'Muhammad Fauzi',   'email' => 'fauzi@matchgo.id'],
            ['name' => 'Nanda Putra',      'email' => 'nanda@matchgo.id'],
            ['name' => 'Oscar Firmansyah', 'email' => 'oscar@matchgo.id'],
            ['name' => 'Pandu Wijaya',     'email' => 'pandu@matchgo.id'],
        ];

        foreach ($players as $player) {
            User::create([
                'name'     => $player['name'],
                'email'    => $player['email'],
                'password' => Hash::make('password'),
                'role'     => 'player',
            ]);
        }
    }
}
