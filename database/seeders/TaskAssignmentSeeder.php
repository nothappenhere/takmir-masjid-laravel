<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\JobResponsibility;
use App\Models\TaskAssignment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        TaskAssignment::query()->delete();

        $committees = Committee::where('active_status', 'active')->get();
        $responsibilities = JobResponsibility::all();

        if ($committees->isEmpty() || $responsibilities->isEmpty()) {
            $this->command->warn('Committees atau JobResponsibilities kosong, TaskAssignmentSeeder dilewati.');
            return;
        }

        $assignments = [];
        $now = now();
        $statuses = ['pending', 'in_progress', 'completed', 'overdue', 'cancelled'];

        // Create assignments for each committee
        foreach ($committees as $committee) {
            // Get responsibilities for committee's position
            $positionResponsibilities = $responsibilities->where('position_id', $committee->position_id);
            
            if ($positionResponsibilities->isEmpty()) {
                // If no specific responsibilities, assign random ones
                $assignedResponsibilities = $responsibilities->random(min(3, $responsibilities->count()));
            } else {
                $assignedResponsibilities = $positionResponsibilities->random(
                    min(2, $positionResponsibilities->count())
                );
            }

            foreach ($assignedResponsibilities as $responsibility) {
                $assignedDate = $now->copy()->subDays(rand(1, 30));
                $dueDate = $assignedDate->copy()->addDays(rand(1, 14));
                
                $status = $statuses[array_rand($statuses)];
                
                // Adjust dates based on status
                $completedDate = null;
                if ($status === 'completed') {
                    $completedDate = $dueDate->copy()->subDays(rand(0, 3));
                } elseif ($status === 'overdue') {
                    $dueDate = $now->copy()->subDays(rand(1, 7));
                }

                $progress = match($status) {
                    'pending' => 0,
                    'in_progress' => rand(20, 80),
                    'completed' => 100,
                    'overdue' => rand(10, 60),
                    'cancelled' => 0,
                };

                $assignments[] = [
                    'committee_id' => $committee->id,
                    'job_responsibility_id' => $responsibility->id,
                    'assigned_date' => $assignedDate,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'progress_percentage' => $progress,
                    'notes' => $this->getAssignmentNotes($status, $responsibility->task_name),
                    'completed_date' => $completedDate,
                    'approved_by' => $status === 'completed' && rand(0, 1) === 1 ? 
                        $committees->where('id', '!=', $committee->id)->random()->id : null,
                    'approved_at' => $completedDate && rand(0, 1) === 1 ? 
                        $completedDate->copy()->addDays(rand(1, 3)) : null,
                    'created_at' => $assignedDate,
                    'updated_at' => $completedDate ?? $assignedDate,
                ];
            }
        }

        // Insert in chunks
        foreach (array_chunk($assignments, 50) as $chunk) {
            TaskAssignment::insert($chunk);
        }

        $this->command->info('Task assignments seeded successfully!');
        $this->command->info('Total assignments: ' . TaskAssignment::count());
        $this->command->info('By status:');
        foreach ($statuses as $status) {
            $count = TaskAssignment::where('status', $status)->count();
            $this->command->info("  {$status}: {$count}");
        }
    }

    private function getAssignmentNotes(string $status, string $taskName): string
    {
        $notes = [
            'pending' => [
                "Menunggu konfirmasi untuk memulai {$taskName}",
                "Akan dimulai dalam waktu dekat",
                "Persiapan awal sedang dilakukan",
            ],
            'in_progress' => [
                "Sedang mengerjakan {$taskName}",
                "Progress berjalan sesuai rencana",
                "Memerlukan koordinasi dengan tim",
            ],
            'completed' => [
                "{$taskName} telah selesai dengan baik",
                "Tuntas sesuai target waktu",
                "Hasil memuaskan, siap untuk review",
            ],
            'overdue' => [
                "{$taskName} tertunda karena kendala teknis",
                "Memerlukan perpanjangan waktu",
                "Sedang dalam proses penyelesaian",
            ],
            'cancelled' => [
                "{$taskName} dibatalkan karena perubahan rencana",
                "Tidak lagi diperlukan",
                "Digantikan dengan tugas lain",
            ],
        ];

        return $notes[$status][array_rand($notes[$status])] ?? 'Catatan penugasan';
    }
}