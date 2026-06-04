<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::firstOrCreate(
            ['email' => 'super@matchgo.id'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'role'     => 'super_admin',
            ]
        );

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@matchgo.id'],
            [
                'name'     => 'Admin MatchGo',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // Auditor
        User::firstOrCreate(
            ['email' => 'auditor@matchgo.id'],
            [
                'name'     => 'Doni Auditor',
                'password' => Hash::make('password'),
                'role'     => 'auditor',
            ]
        );

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
            User::firstOrCreate(
                ['email' => $player['email']],
                [
                    'name'     => $player['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'player',
                ]
            );
        }
    }
}
