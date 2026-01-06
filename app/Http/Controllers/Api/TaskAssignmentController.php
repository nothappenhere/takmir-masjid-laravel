<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskAssignmentRequest;
use App\Http\Requests\UpdateTaskAssignmentRequest;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = TaskAssignment::with(['committee', 'jobResponsibility.position'])
                ->with(['approver']);

            // Filters
            if ($request->filled('committee_id')) {
                $query->where('committee_id', $request->committee_id);
            }

            if ($request->filled('job_responsibility_id')) {
                $query->where('job_responsibility_id', $request->job_responsibility_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->whereHas('jobResponsibility', function ($q) use ($request) {
                    $q->where('priority', $request->priority);
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('assigned_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('assigned_date', '<=', $request->date_to);
            }

            if ($request->filled('due_date_from')) {
                $query->whereDate('due_date', '>=', $request->due_date_from);
            }

            if ($request->filled('due_date_to')) {
                $query->whereDate('due_date', '<=', $request->due_date_to);
            }

            if ($request->filled('overdue')) {
                $query->where('due_date', '<', today())
                    ->whereIn('status', ['pending', 'in_progress']);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('committee', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                    })->orWhereHas('jobResponsibility', function ($q) use ($search) {
                        $q->where('task_name', 'like', "%{$search}%");
                    });
                });
            }

            // Sorting
            $sortField = $request->query('sort_by', 'due_date');
            $sortDirection = $request->query('sort_dir', 'asc');

            // Special sorting for status
            if ($sortField === 'status') {
                $query->orderByRaw("FIELD(status, 'overdue', 'pending', 'in_progress', 'completed', 'cancelled')");
            } else {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->query('per_page', 20);
            $assignments = $query->paginate($perPage);

            return ResponseHelper::paginated($request, $assignments, 'Daftar penugasan berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil data penugasan', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskAssignmentRequest $request)
    {
        try {
            $data = $request->validated();

            // Check if committee is active
            $committee = \App\Models\Committee::find($data['committee_id']);
            if ($committee->active_status !== 'active') {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menugaskan tugas kepada pengurus yang tidak aktif',
                    null,
                    422
                );
            }

            // Set default due date if not provided (7 days from assignment)
            if (!isset($data['due_date'])) {
                $data['due_date'] = Carbon::parse($data['assigned_date'])->addDays(7);
            }

            // Check for overdue tasks
            if (Carbon::parse($data['due_date'])->lt(today())) {
                $data['status'] = 'overdue';
            }

            $assignment = TaskAssignment::create($data);

            return ResponseHelper::success(
                $request,
                $assignment->load(['committee', 'jobResponsibility.position', 'approver']),
                'Penugasan berhasil dibuat',
                201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal membuat penugasan', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $assignment = TaskAssignment::with([
                'committee.position',
                'jobResponsibility.position',
                'approver'
            ])->findOrFail($id);

            return ResponseHelper::success($request, $assignment, 'Detail penugasan berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Penugasan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil detail penugasan', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskAssignmentRequest $request, $id)
    {
        try {
            $assignment = TaskAssignment::findOrFail($id);
            $data = $request->validated();

            // Update status based on progress
            if (isset($data['progress_percentage'])) {
                if ($data['progress_percentage'] == 100 && $assignment->status !== 'completed') {
                    $data['status'] = 'completed';
                    $data['completed_date'] = now();
                } elseif ($data['progress_percentage'] > 0 && $assignment->status === 'pending') {
                    $data['status'] = 'in_progress';
                }
            }

            // Mark as overdue if due date passed
            if (isset($data['due_date']) && Carbon::parse($data['due_date'])->lt(today())) {
                if (!in_array($assignment->status, ['completed', 'cancelled'])) {
                    $data['status'] = 'overdue';
                }
            }

            // If marking as completed, set progress to 100 and completion date
            if (isset($data['status']) && $data['status'] === 'completed') {
                $data['progress_percentage'] = 100;
                $data['completed_date'] = $data['completed_date'] ?? now();
            }

            $assignment->update($data);

            return ResponseHelper::success(
                $request,
                $assignment->load(['committee', 'jobResponsibility.position', 'approver']),
                'Penugasan berhasil diperbarui'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Penugasan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal memperbarui penugasan', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $assignment = TaskAssignment::findOrFail($id);

            // Cannot delete completed or approved assignments
            if ($assignment->status === 'completed') {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menghapus penugasan yang sudah selesai',
                    null,
                    409
                );
            }

            if ($assignment->approved_by) {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menghapus penugasan yang sudah disetujui',
                    null,
                    409
                );
            }

            $assignment->delete();

            return ResponseHelper::success($request, null, 'Penugasan berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Penugasan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menghapus penugasan', $e->getMessage(), 500);
        }
    }

    /**
     * Update task progress
     */
    public function updateProgress(Request $request, $id)
    {
        try {
            $request->validate([
                'progress_percentage' => 'required|integer|min:0|max:100',
                'notes' => 'nullable|string',
            ]);

            $assignment = TaskAssignment::findOrFail($id);

            $oldProgress = $assignment->progress_percentage;
            $newProgress = $request->progress_percentage;

            $data = [
                'progress_percentage' => $newProgress,
                'notes' => $request->notes ?? $assignment->notes,
            ];

            // Update status based on progress
            if ($newProgress == 100 && $assignment->status !== 'completed') {
                $data['status'] = 'completed';
                $data['completed_date'] = now();
            } elseif ($newProgress > 0 && $assignment->status === 'pending') {
                $data['status'] = 'in_progress';
            }

            $assignment->update($data);

            return ResponseHelper::success(
                $request,
                $assignment,
                "Progres tugas berhasil diubah dari {$oldProgress}% menjadi {$newProgress}%"
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Penugasan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengubah progres', $e->getMessage(), 500);
        }
    }

    /**
     * Approve task assignment
     */
    public function approve(Request $request, $id)
    {
        try {
            $request->validate([
                'approver_id' => 'required|exists:committees,id',
                'notes' => 'nullable|string',
            ]);

            $assignment = TaskAssignment::findOrFail($id);

            // Can only approve completed tasks
            if ($assignment->status !== 'completed') {
                return ResponseHelper::error(
                    $request,
                    'Hanya dapat menyetujui tugas yang sudah selesai',
                    null,
                    422
                );
            }

            // Check if already approved
            if ($assignment->approved_by) {
                return ResponseHelper::error(
                    $request,
                    'Tugas sudah disetujui sebelumnya',
                    null,
                    409
                );
            }

            $assignment->update([
                'approved_by' => $request->approver_id,
                'approved_at' => now(),
                'notes' => $request->notes ?? $assignment->notes,
            ]);

            return ResponseHelper::success(
                $request,
                $assignment->load('approver'),
                'Tugas berhasil disetujui'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Penugasan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menyetujui tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Get statistics for task assignments
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total' => TaskAssignment::count(),
                'by_status' => TaskAssignment::selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->get()
                    ->pluck('count', 'status'),
                'overdue' => TaskAssignment::where('due_date', '<', today())
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count(),
                'completed_this_month' => TaskAssignment::where('status', 'completed')
                    ->whereMonth('completed_date', now()->month)
                    ->whereYear('completed_date', now()->year)
                    ->count(),
                'average_completion_time' => TaskAssignment::where('status', 'completed')
                    ->whereNotNull('completed_date')
                    ->whereNotNull('assigned_date')
                    ->selectRaw('AVG(DATEDIFF(completed_date, assigned_date)) as avg_days')
                    ->first()->avg_days ?? 0,
                'approval_rate' => TaskAssignment::where('status', 'completed')
                    ->selectRaw('COUNT(CASE WHEN approved_by IS NOT NULL THEN 1 END) / COUNT(*) * 100 as rate')
                    ->first()->rate ?? 0,
            ];

            return ResponseHelper::success($request, $stats, 'Statistik penugasan berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil statistik', $e->getMessage(), 500);
        }
    }

    /**
     * Get overdue tasks
     */
    public function overdue(Request $request)
    {
        try {
            $overdueTasks = TaskAssignment::with(['committee', 'jobResponsibility'])
                ->where('due_date', '<', today())
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderBy('due_date')
                ->paginate($request->query('per_page', 20));

            return ResponseHelper::paginated($request, $overdueTasks, 'Tugas terlambat berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil tugas terlambat', $e->getMessage(), 500);
        }
    }
}
