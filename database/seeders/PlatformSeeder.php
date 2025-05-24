<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Platform;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Platform::create([
            'name' => 'Twitter',
            'type' => 'twitter',
            'is_active' => true,
        ]);
        Platform::create([
            'name' => 'Instagram',
            'type' => 'instagram',
            'is_active' => true,
        ]);
        Platform::create([
            'name' => 'LinkedIn',
            'type' => 'linkedin',
            'is_active' => true,
        ]);
    }
}
