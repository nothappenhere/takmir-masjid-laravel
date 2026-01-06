<?php

namespace Database\Seeders;

use App\Models\JobResponsibility;
use App\Models\Position;
use Illuminate\Database\Seeder;

class JobResponsibilitySeeder extends Seeder
{
    public function run(): void
    {
        JobResponsibility::query()->delete();

        $positions = Position::all()->keyBy('name');

        $responsibilities = [
            // Ketua Takmir
            [
                'position_id' => $positions['Ketua Takmir']->id,
                'task_name' => 'Memimpin rapat takmir',
                'task_description' => 'Memimpin rapat rutin bulanan takmir masjid',
                'priority' => 'high',
                'estimated_hours' => 3,
                'frequency' => 'monthly',
                'is_core_responsibility' => true,
            ],
            [
                'position_id' => $positions['Ketua Takmir']->id,
                'task_name' => 'Menyusun program kerja',
                'task_description' => 'Menyusun program kerja tahunan masjid',
                'priority' => 'high',
                'estimated_hours' => 10,
                'frequency' => 'yearly',
                'is_core_responsibility' => true,
            ],

            // Sekretaris
            [
                'position_id' => $positions['Sekretaris']->id,
                'task_name' => 'Membuat notulen rapat',
                'task_description' => 'Mencatat dan membuat notulen setiap rapat',
                'priority' => 'high',
                'estimated_hours' => 2,
                'frequency' => 'monthly',
                'is_core_responsibility' => true,
            ],
            [
                'position_id' => $positions['Sekretaris']->id,
                'task_name' => 'Mengarsipkan dokumen',
                'task_description' => 'Mengelola dan mengarsipkan dokumen masjid',
                'priority' => 'medium',
                'estimated_hours' => 4,
                'frequency' => 'monthly',
                'is_core_responsibility' => true,
            ],

            // Bendahara
            [
                'position_id' => $positions['Bendahara']->id,
                'task_name' => 'Membuat laporan keuangan',
                'task_description' => 'Membuat laporan keuangan bulanan masjid',
                'priority' => 'high',
                'estimated_hours' => 8,
                'frequency' => 'monthly',
                'is_core_responsibility' => true,
            ],
            [
                'position_id' => $positions['Bendahara']->id,
                'task_name' => 'Mengelola kas masjid',
                'task_description' => 'Mengelola penerimaan dan pengeluaran kas',
                'priority' => 'high',
                'estimated_hours' => 5,
                'frequency' => 'weekly',
                'is_core_responsibility' => true,
            ],

            // Koordinator Kebersihan
            [
                'position_id' => $positions['Koordinator Kebersihan']->id,
                'task_name' => 'Menyusun jadwal piket',
                'task_description' => 'Menyusun jadwal piket kebersihan mingguan',
                'priority' => 'medium',
                'estimated_hours' => 2,
                'frequency' => 'weekly',
                'is_core_responsibility' => true,
            ],
            [
                'position_id' => $positions['Koordinator Kebersihan']->id,
                'task_name' => 'Memeriksa kebersihan masjid',
                'task_description' => 'Memeriksa kebersihan seluruh area masjid',
                'priority' => 'medium',
                'estimated_hours' => 3,
                'frequency' => 'daily',
                'is_core_responsibility' => true,
            ],

            // Petugas Kebersihan
            [
                'position_id' => $positions['Petugas Kebersihan']->id,
                'task_name' => 'Membersihkan area sholat',
                'task_description' => 'Menyapu dan mengepel area sholat utama',
                'priority' => 'high',
                'estimated_hours' => 2,
                'frequency' => 'daily',
                'is_core_responsibility' => true,
            ],
            [
                'position_id' => $positions['Petugas Kebersihan']->id,
                'task_name' => 'Membersihkan kamar mandi',
                'task_description' => 'Membersihkan dan mensterilkan kamar mandi/wudhu',
                'priority' => 'high',
                'estimated_hours' => 1,
                'frequency' => 'daily',
                'is_core_responsibility' => true,
            ],

            // Koordinator Keamanan
            [
                'position_id' => $positions['Koordinator Keamanan']->id,
                'task_name' => 'Mengatur jaga malam',
                'task_description' => 'Mengatur jadwal jaga malam masjid',
                'priority' => 'high',
                'estimated_hours' => 3,
                'frequency' => 'monthly',
                'is_core_responsibility' => true,
            ],
            [
                'position_id' => $positions['Koordinator Keamanan']->id,
                'task_name' => 'Memeriksa keamanan',
                'task_description' => 'Memeriksa sistem keamanan dan CCTV',
                'priority' => 'medium',
                'estimated_hours' => 2,
                'frequency' => 'weekly',
                'is_core_responsibility' => true,
            ],
        ];

        foreach ($responsibilities as $responsibility) {
            JobResponsibility::create($responsibility);
        }

        $this->command->info('Job responsibilities seeded successfully!');
    }
}
