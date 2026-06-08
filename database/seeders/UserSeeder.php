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
            ['name' => 'Budi Santoso',     'email' => 'budi@matchgo.id',   'whatsapp' => '6281234567801'],
            ['name' => 'Rizky Pratama',    'email' => 'rizky@matchgo.id',  'whatsapp' => '6281234567802'],
            ['name' => 'Andi Kurniawan',   'email' => 'andi@matchgo.id',   'whatsapp' => '6281234567803'],
            ['name' => 'Dimas Prasetyo',   'email' => 'dimas@matchgo.id',  'whatsapp' => '6281234567804'],
            ['name' => 'Fajar Nugroho',    'email' => 'fajar@matchgo.id',  'whatsapp' => '6281234567805'],
            ['name' => 'Galih Wicaksono',  'email' => 'galih@matchgo.id',  'whatsapp' => '6281234567806'],
            ['name' => 'Hendra Gunawan',   'email' => 'hendra@matchgo.id', 'whatsapp' => '6281234567807'],
            ['name' => 'Irfan Hakim',      'email' => 'irfan@matchgo.id',  'whatsapp' => '6281234567808'],
            ['name' => 'Joko Widodo',      'email' => 'joko@matchgo.id',   'whatsapp' => '6281234567809'],
            ['name' => 'Kevin Sanjaya',    'email' => 'kevin@matchgo.id',  'whatsapp' => '6281234567810'],
            ['name' => 'Luthfi Rahman',    'email' => 'luthfi@matchgo.id', 'whatsapp' => '6281234567811'],
            ['name' => 'Muhammad Fauzi',   'email' => 'fauzi@matchgo.id',  'whatsapp' => '6281234567812'],
            ['name' => 'Nanda Putra',      'email' => 'nanda@matchgo.id',  'whatsapp' => '6281234567813'],
            ['name' => 'Oscar Firmansyah', 'email' => 'oscar@matchgo.id',  'whatsapp' => '6281234567814'],
            ['name' => 'Pandu Wijaya',     'email' => 'pandu@matchgo.id',  'whatsapp' => '6281234567815'],
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
