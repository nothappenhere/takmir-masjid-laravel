<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\DutySchedule;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DutyScheduleSeeder extends Seeder
{
    public function run(): void
    {
        DutySchedule::query()->delete();

        $committees = Committee::where('active_status', 'active')->get();

        if ($committees->isEmpty()) {
            $this->command->warn('Tidak ada pengurus aktif, DutyScheduleSeeder dilewati.');
            return;
        }

        $dutyTypes = ['piket', 'kebersihan', 'keamanan', 'administrasi'];
        $locations = ['Gedung Utama', 'Ruang Sholat Utama', 'Tempat Wudhu', 'Ruang Administrasi', 'Halaman Masjid'];
        $statuses = ['pending', 'ongoing', 'completed'];

        $schedules = [];

        // Create schedules for next 14 days
        for ($day = 1; $day <= 14; $day++) {
            $date = Carbon::now()->addDays($day);

            // Skip Fridays (Jumuah) for certain duties
            if ($date->dayOfWeek === Carbon::FRIDAY) {
                continue;
            }

            // Assign 2-3 committees per day
            $dailyCommittees = $committees->random(min(3, $committees->count()));

            foreach ($dailyCommittees as $committee) {
                $dutyType = $dutyTypes[array_rand($dutyTypes)];

                // Set time based on duty type
                if ($dutyType === 'keamanan') {
                    $startTime = '20:00';
                    $endTime = '06:00';
                } elseif ($dutyType === 'piket') {
                    $startTime = '08:00';
                    $endTime = '12:00';
                } else {
                    $startTime = '09:00';
                    $endTime = '15:00';
                }

                $schedules[] = [
                    'committee_id' => $committee->id,
                    'duty_date' => $date->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location' => $locations[array_rand($locations)],
                    'duty_type' => $dutyType,
                    'status' => $date->isToday() ? 'ongoing' : 'pending',
                    'remarks' => $this->getRemarks($dutyType),
                    'is_recurring' => $dutyType === 'piket' && rand(0, 1) === 1,
                    'recurring_type' => $dutyType === 'piket' ? 'weekly' : null,
                    'recurring_end_date' => $dutyType === 'piket' ? $date->copy()->addWeeks(4) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Create some past completed schedules
        for ($day = 1; $day <= 7; $day++) {
            $date = Carbon::now()->subDays($day);
            $pastCommittees = $committees->random(min(2, $committees->count()));

            foreach ($pastCommittees as $committee) {
                $schedules[] = [
                    'committee_id' => $committee->id,
                    'duty_date' => $date->toDateString(),
                    'start_time' => '08:00',
                    'end_time' => '12:00',
                    'location' => $locations[array_rand($locations)],
                    'duty_type' => 'kebersihan',
                    'status' => 'completed',
                    'remarks' => 'Tugas kebersihan harian',
                    'is_recurring' => false,
                    'recurring_type' => null,
                    'recurring_end_date' => null,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }
        }

        // Insert in chunks
        foreach (array_chunk($schedules, 50) as $chunk) {
            DutySchedule::insert($chunk);
        }

        $this->command->info('Duty schedules seeded successfully!');
        $this->command->info('Total schedules: ' . count($schedules));
        $this->command->info('Pending: ' . DutySchedule::where('status', 'pending')->count());
        $this->command->info('Ongoing: ' . DutySchedule::where('status', 'ongoing')->count());
        $this->command->info('Completed: ' . DutySchedule::where('status', 'completed')->count());
    }

    private function getRemarks(string $dutyType): string
    {
        $remarks = [
            'piket' => [
                'Piket rutin harian',
                'Bertanggung jawab atas kelancaran operasional',
                'Menerima tamu dan mengatur kegiatan',
            ],
            'kebersihan' => [
                'Membersihkan area sholat',
                'Mengepel lantai dan merapikan sajadah',
                'Membersihkan tempat wudhu dan kamar mandi',
            ],
            'keamanan' => [
                'Jaga malam masjid',
                'Memastikan keamanan properti masjid',
                'Mengawasi CCTV dan pintu masuk',
            ],
            'administrasi' => [
                'Mengelola administrasi harian',
                'Mencatat keuangan dan donasi',
                'Membuat laporan kegiatan',
            ],
        ];

        return $remarks[$dutyType][array_rand($remarks[$dutyType])] ?? 'Tugas rutin masjid';
    }
}
