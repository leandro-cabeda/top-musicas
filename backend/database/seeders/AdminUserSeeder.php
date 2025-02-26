<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Verifica se o usuário já existe para evitar duplicação
        if (!User::where('email', 'leandro.admin@hotmail.com')->exists()) {
            User::create([
                'name' => 'Leandro Admin',
                'email' => 'leandro.admin@hotmail.com',
                'password' => Hash::make('123456'),
                'role' => 'admin',
            ]);
        }
    }
}
