<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'ppid@empatlawangkab.go.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        // Owner's main admin (task #127). Every user can log in to /admin, so this is full access.
        // The repo default password is weak: production only gets this user when ADMIN_PASSWORD is set in env.
        $password = config('services.admin.password') ?: (app()->isProduction() ? null : 'qwe123');
        if (! $password) {
            $this->command?->warn('UserSeeder: ADMIN_PASSWORD kosong di production — user admin utama tidak dibuat.');

            return;
        }
        User::firstOrCreate(
            ['email' => config('services.admin.email') ?: 'rezaharjadinata4199@gmail.com'],
            [
                'name' => 'Reza Harjadinata',
                'password' => Hash::make($password),
            ]
        );
    }
}
