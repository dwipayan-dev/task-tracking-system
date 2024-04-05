<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        $admin = [
            'name' => "Super Admin",
            'email' => "superadmin@quocent.com",
            'password' => Hash::make('superadmin'),
            'uuid' => Str::uuid(),
            'status' => 1,
        ];
        $user = User::create($admin);
        $user->addRole('admin');
    }
}
