<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'editor@thesoccergoals.com'],
            [
                'name' => 'Editorial Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
    }
}
