<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus user yang sudah ada (opsional)
        User::query()->delete();

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@masjid.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ketua Takmir',
                'email' => 'ketua@masjid.com',
                'password' => Hash::make('ketua123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Wakil Ketua Takmir',
                'email' => 'wakilketua@masjid.com',
                'password' => Hash::make('wakilketua123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sekretaris',
                'email' => 'sekretaris@masjid.com',
                'password' => Hash::make('sekretaris123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bendahara',
                'email' => 'bendahara@masjid.com',
                'password' => Hash::make('bendahara123'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Total users: ' . User::count());

        // Tampilkan info login
        $this->command->info("\nLogin credentials:");
        $this->command->info("Email: superadmin@masjid.com | Password: password123");
        $this->command->info("Email: ketua@masjid.com | Password: ketua123");
        $this->command->info("Email: wakilketua@masjid.com | Password: wakilketua123");
        $this->command->info("Email: sekretaris@masjid.com | Password: sekretaris123");
        $this->command->info("Email: bendahara@masjid.com | Password: bendahara123");
    }
}
