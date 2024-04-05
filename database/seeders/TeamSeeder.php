<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'PHP',
        ]);
        Team::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'Odoo',
        ]);
        Team::create([
            'uuid' => Str::uuid()->toString(),
            'name' => 'Dotnet',
        ]);
    }
}
