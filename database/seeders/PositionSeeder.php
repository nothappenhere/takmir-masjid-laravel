<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing positions
        Position::query()->delete();

        $positions = [
            // Leadership (level 1)
            [
                'name' => 'Ketua Takmir',
                'slug' => 'ketua-takmir',
                'description' => 'Memimpin seluruh kegiatan takmir masjid',
                'parent_id' => null,
                'order' => 1,
                'status' => 'active',
                'level' => 'leadership',
            ],
            [
                'name' => 'Wakil Ketua',
                'slug' => 'wakil-ketua',
                'description' => 'Membantu ketua dalam memimpin takmir',
                'parent_id' => 1,
                'order' => 2,
                'status' => 'active',
                'level' => 'leadership',
            ],
            [
                'name' => 'Sekretaris',
                'slug' => 'sekretaris',
                'description' => 'Mengurus administrasi dan dokumentasi',
                'parent_id' => 1,
                'order' => 3,
                'status' => 'active',
                'level' => 'leadership',
            ],
            [
                'name' => 'Bendahara',
                'slug' => 'bendahara',
                'description' => 'Mengelola keuangan masjid',
                'parent_id' => 1,
                'order' => 4,
                'status' => 'active',
                'level' => 'leadership',
            ],

            // Division Heads (level 2)
            [
                'name' => 'Koordinator Kebersihan',
                'slug' => 'koordinator-kebersihan',
                'description' => 'Mengkoordinasi kebersihan masjid',
                'parent_id' => 1,
                'order' => 5,
                'status' => 'active',
                'level' => 'division_head',
            ],
            [
                'name' => 'Koordinator Keamanan',
                'slug' => 'koordinator-keamanan',
                'description' => 'Mengkoordinasi keamanan masjid',
                'parent_id' => 1,
                'order' => 6,
                'status' => 'active',
                'level' => 'division_head',
            ],
            [
                'name' => 'Koordinator Acara',
                'slug' => 'koordinator-acara',
                'description' => 'Mengkoordinasi acara dan kegiatan',
                'parent_id' => 1,
                'order' => 7,
                'status' => 'active',
                'level' => 'division_head',
            ],

            // Staff (level 3)
            [
                'name' => 'Petugas Kebersihan',
                'slug' => 'petugas-kebersihan',
                'description' => 'Bertanggung jawab atas kebersihan masjid',
                'parent_id' => 5, // Koordinator Kebersihan
                'order' => 8,
                'status' => 'active',
                'level' => 'staff',
            ],
            [
                'name' => 'Petugas Keamanan',
                'slug' => 'petugas-keamanan',
                'description' => 'Bertanggung jawab atas keamanan masjid',
                'parent_id' => 6, // Koordinator Keamanan
                'order' => 9,
                'status' => 'active',
                'level' => 'staff',
            ],
            [
                'name' => 'Petugas Sound System',
                'slug' => 'petugas-sound-system',
                'description' => 'Mengelola sound system dan peralatan audio',
                'parent_id' => 7, // Koordinator Acara
                'order' => 10,
                'status' => 'active',
                'level' => 'staff',
            ],

            // Volunteers (level 4)
            [
                'name' => 'Relawan Kebersihan',
                'slug' => 'relawan-kebersihan',
                'description' => 'Relawan untuk kegiatan kebersihan',
                'parent_id' => 8, // Petugas Kebersihan
                'order' => 11,
                'status' => 'active',
                'level' => 'volunteer',
            ],
            [
                'name' => 'Relawan Acara',
                'slug' => 'relawan-acara',
                'description' => 'Relawan untuk membantu acara',
                'parent_id' => 10, // Petugas Sound System
                'order' => 12,
                'status' => 'active',
                'level' => 'volunteer',
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }

        $this->command->info('Positions seeded successfully!');
    }
}
