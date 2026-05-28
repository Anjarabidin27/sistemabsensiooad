<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'admin@udinus.ac.id'],
            [
                'name'     => 'Administrator SIHADIR',
                'email'    => 'admin@udinus.ac.id',
                'password' => Hash::make('admin123'),
            ]
        );

        $this->command->info('Admin user seeded: admin@udinus.ac.id / admin123');
    }
}
