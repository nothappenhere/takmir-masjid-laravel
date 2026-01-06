<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\Position;
use App\Models\PositionHistory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PositionHistorySeeder extends Seeder
{
    public function run(): void
    {
        PositionHistory::query()->delete();

        $committees = Committee::all();
        $positions = Position::all();

        if ($committees->isEmpty() || $positions->isEmpty()) {
            $this->command->warn('Committees atau Positions kosong, PositionHistorySeeder dilewati.');
            return;
        }

        $histories = [];
        $now = now();

        foreach ($committees as $committee) {
            // Create current active position history
            if ($committee->position_id && $committee->active_status === 'active') {
                $histories[] = [
                    'committee_id' => $committee->id,
                    'position_id' => $committee->position_id,
                    'start_date' => $committee->join_date ?? $now->subYears(1),
                    'end_date' => null,
                    'is_active' => true,
                    'appointment_type' => 'permanent',
                    'remarks' => 'Penunjukan awal sebagai ' . $positions->find($committee->position_id)->name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Create past position history for some committees
            if (rand(0, 1) === 1 && $committee->position_id) {
                $pastPosition = $positions->where('id', '!=', $committee->position_id)->random();

                $startDate = Carbon::parse($committee->join_date ?? $now->subYears(2))->subMonths(6);
                $endDate = $committee->join_date ?? $now->subYears(1);

                $histories[] = [
                    'committee_id' => $committee->id,
                    'position_id' => $pastPosition->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => false,
                    'appointment_type' => collect(['permanent', 'temporary'])->random(),
                    'remarks' => 'Sebelumnya menjabat sebagai ' . $pastPosition->name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // For inactive/resigned committees, end their position history
            if (in_array($committee->active_status, ['inactive', 'resigned']) && $committee->position_id) {
                PositionHistory::where('committee_id', $committee->id)
                    ->where('is_active', true)
                    ->update([
                        'end_date' => $committee->updated_at ?? $now->subMonths(1),
                        'is_active' => false,
                        'remarks' => 'Berakhir karena status ' . $committee->active_status,
                    ]);
            }
        }

        // Insert histories
        foreach (array_chunk($histories, 50) as $chunk) {
            PositionHistory::insert($chunk);
        }

        $this->command->info('Position histories seeded successfully!');
        $this->command->info('Total histories: ' . PositionHistory::count());
        $this->command->info('Active histories: ' . PositionHistory::where('is_active', true)->count());
    }
}
