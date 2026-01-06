<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Committee;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CommitteeSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing committees
        Committee::query()->delete();

        $positions = Position::all()->keyBy('name');
        $users = User::all()->keyBy('email');
        $now = now();

        $committees = [
            [
                'full_name' => 'Ahmad Fadli',
                'email' => 'ahmad.fadli@example.com',
                'phone_number' => '081234567890',
                'gender' => 'male',
                'birth_date' => '1990-04-12',
                'address' => 'Jl. Melati No. 23, Jakarta Selatan',
                'join_date' => $now->subYears(3),
                'active_status' => 'active',
                'position_id' => $positions['Ketua Takmir']->id,
                'user_id' => $users['ketua@masjid.com']->id,
                'photo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'full_name' => 'Siti Aminah',
                'email' => 'siti.aminah@example.com',
                'phone_number' => '082345678901',
                'gender' => 'female',
                'birth_date' => '1992-07-05',
                'address' => 'Jl. Mawar No. 10, Jakarta Timur',
                'join_date' => $now->subYears(2),
                'active_status' => 'active',
                'position_id' => $positions['Wakil Ketua']->id,
                'user_id' => $users['wakilketua@masjid.com']->id,
                'photo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'full_name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'phone_number' => '083456789012',
                'gender' => 'male',
                'birth_date' => '1988-11-18',
                'address' => 'Jl. Kenanga No. 7, Jakarta Barat',
                'join_date' => $now->subYears(4),
                'active_status' => 'active',
                'position_id' => $positions['Sekretaris']->id,
                'user_id' => $users['sekretaris@masjid.com']->id,
                'photo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'full_name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@example.com',
                'phone_number' => '084567890123',
                'gender' => 'female',
                'birth_date' => '1991-03-22',
                'address' => 'Jl. Anggrek No. 15, Jakarta Utara',
                'join_date' => $now->subYears(2),
                'active_status' => 'active',
                'position_id' => $positions['Bendahara']->id,
                'user_id' => $users['bendahara@masjid.com']->id,
                'photo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'full_name' => 'Rizki Pratama',
                'email' => 'rizki.pratama@example.com',
                'phone_number' => '085678901234',
                'gender' => 'male',
                'birth_date' => '1995-01-30',
                'address' => 'Jl. Flamboyan No. 9, Jakarta Pusat',
                'join_date' => $now->subYear(),
                'active_status' => 'active',
                'position_id' => $positions['Koordinator Kebersihan']->id,
                'user_id' => null, // Tidak ada user untuk koordinator
                'photo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'full_name' => 'Maya Indah',
                'email' => 'maya.indah@example.com',
                'phone_number' => '086789012345',
                'gender' => 'female',
                'birth_date' => '1993-08-14',
                'address' => 'Jl. Cempaka No. 12, Depok',
                'join_date' => $now->subMonths(18),
                'active_status' => 'inactive',
                'position_id' => $positions['Petugas Kebersihan']->id,
                'user_id' => null, // Tidak ada user untuk petugas
                'photo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'full_name' => 'Joko Susilo',
                'email' => 'joko.susilo@example.com',
                'phone_number' => '087890123456',
                'gender' => 'male',
                'birth_date' => '1985-12-05',
                'address' => 'Jl. Dahlia No. 18, Tangerang',
                'join_date' => $now->subYears(5),
                'active_status' => 'resigned',
                'position_id' => $positions['Relawan Kebersihan']->id,
                'user_id' => null, // Tidak ada user untuk relawan
                'photo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($committees as $committee) {
            Committee::create($committee);
        }

        $this->command->info('Committees seeded successfully!');
        $this->command->info('Active: ' . Committee::where('active_status', 'active')->count());
        $this->command->info('Inactive: ' . Committee::where('active_status', 'inactive')->count());
        $this->command->info('Resigned: ' . Committee::where('active_status', 'resigned')->count());
    }
}
