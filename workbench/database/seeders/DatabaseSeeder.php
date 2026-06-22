<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\Schedule;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo', 'password' => Hash::make('password')],
        );

        $samples = [
            ['name' => 'Every minute', 'schedule' => '* * * * *'],
            ['name' => 'Weekdays at 04:00', 'schedule' => '0 4 * * 1-5'],
            ['name' => 'Every 15 min, business hours', 'schedule' => '*/15 4,12,20 * * 1-5'],
        ];

        foreach ($samples as $sample) {
            Schedule::query()->firstOrCreate(['name' => $sample['name']], $sample);
        }
    }
}
