<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,                   // 1. Buat users dulu
            PositionSeeder::class,               // 2. Buat positions
            CommitteeSeeder::class,              // 3. Buat committees (butuh users & positions)
            JobResponsibilitySeeder::class,      // 4. Buat job responsibilities (butuh positions)
            PositionHistorySeeder::class,        // 5. Buat position histories (butuh committees & positions)
            DutyScheduleSeeder::class,           // 6. Buat duty schedules (butuh committees)
            TaskAssignmentSeeder::class,         // 7. Buat task assignments (butuh committees & job responsibilities)
            OrganizationalStructureSeeder::class, // 8. Buat organizational structure (butuh positions)
        ]);
    }
}
