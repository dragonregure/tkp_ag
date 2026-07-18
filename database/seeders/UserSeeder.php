<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'email' => 'admin@example.com', 'role' => Roles::ADMIN],
            ['name' => 'Maya Prameswari', 'email' => 'maya@example.com', 'role' => Roles::MANAGER],
            ['name' => 'Raka Saputra', 'email' => 'raka@example.com', 'role' => Roles::SALES],
            ['name' => 'Nadia Kusuma', 'email' => 'nadia@example.com', 'role' => Roles::CASHIER],
            ['name' => 'Bima Hartono', 'email' => 'bima@example.com', 'role' => Roles::INVENTORY],
        ];

        foreach ($users as $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$row['role']]);
        }
    }
}
