<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TaskAssignment;
use App\Models\Committee;
use App\Models\JobResponsibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskAssignmentController extends Controller
{
    /**
     * Display a listing of the task assignments.
     */
    public function index(Request $request)
    {
        // Query with relationships
        $query = TaskAssignment::with(['committee.position', 'jobResponsibility.position', 'approver'])
            ->latest('assigned_date');

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('committee', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('jobResponsibility', function ($q) use ($search) {
                        $q->where('task_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by committee
        if ($request->has('committee_id') && $request->committee_id != '') {
            $query->where('committee_id', $request->committee_id);
        }

        // Filter by job responsibility
        if ($request->has('job_responsibility_id') && $request->job_responsibility_id != '') {
            $query->where('job_responsibility_id', $request->job_responsibility_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by priority (through job responsibility)
        if ($request->has('priority') && $request->priority != '') {
            $query->whereHas('jobResponsibility', function ($q) use ($request) {
                $q->where('priority', $request->priority);
            });
        }

        // Filter by date
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('assigned_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('assigned_date', '<=', $request->date_to);
        }

        // Filter by due date
        if ($request->has('due_date_from') && $request->due_date_from != '') {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }

        if ($request->has('due_date_to') && $request->due_date_to != '') {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }

        // Filter overdue tasks
        if ($request->has('overdue') && $request->boolean('overdue')) {
            $query->where('due_date', '<', today())
                ->whereIn('status', ['pending', 'in_progress']);
        }

        // Filter due soon tasks (within 3 days)
        if ($request->has('due_soon') && $request->boolean('due_soon')) {
            $query->whereBetween('due_date', [today(), today()->addDays(3)])
                ->whereIn('status', ['pending', 'in_progress']);
        }

        // Get active committees for filter
        $committees = Committee::where('active_status', 'active')
            ->orderBy('full_name')
            ->get();

        // Get job responsibilities for filter
        $jobResponsibilities = JobResponsibility::with('position')
            ->orderBy('task_name')
            ->get();

        // Pagination
        $assignments = $query->paginate(20)->withQueryString();

        return view('task-assignments.index', compact(
            'assignments',
            'committees',
            'jobResponsibilities'
        ));
    }

    /**
     * Show the form for creating a new task assignment.
     */
    public function create()
    {
        $committees = Committee::where('active_status', 'active')
            ->orderBy('full_name')
            ->get();

        $jobResponsibilities = JobResponsibility::with('position')
            ->orderBy('task_name')
            ->get();

        // Default values
        $defaultDate = now()->format('Y-m-d');
        $defaultDueDate = now()->addDays(7)->format('Y-m-d');

        return view('task-assignments.create', compact(
            'committees',
            'jobResponsibilities',
            'defaultDate',
            'defaultDueDate'
        ));
    }

    /**
     * Store a newly created task assignment in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'job_responsibility_id' => 'required|exists:job_responsibilities,id',
            'assigned_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:assigned_date',
            'status' => 'required|in:pending,in_progress,completed,overdue,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        // Check if committee is active
        $committee = Committee::find($validated['committee_id']);
        if ($committee->active_status != 'active') {
            return back()->withInput()
                ->with('error', 'Tidak dapat menugaskan tugas kepada pengurus yang tidak aktif.');
        }

        // Set default due date if not provided (7 days from assignment)
        if (empty($validated['due_date'])) {
            $validated['due_date'] = Carbon::parse($validated['assigned_date'])->addDays(7);
        }

        // Adjust status based on due date
        if (Carbon::parse($validated['due_date'])->lt(today())) {
            if (!in_array($validated['status'], ['completed', 'cancelled'])) {
                $validated['status'] = 'overdue';
            }
        }

        // Set progress percentage based on status
        if ($validated['status'] == 'completed' && empty($validated['progress_percentage'])) {
            $validated['progress_percentage'] = 100;
        } elseif ($validated['status'] == 'pending' && empty($validated['progress_percentage'])) {
            $validated['progress_percentage'] = 0;
        }

        try {
            $assignment = TaskAssignment::create($validated);

            return redirect()->route('task-assignments.show', $assignment->id)
                ->with('success', 'Penugasan berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal membuat penugasan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified task assignment.
     */
    public function show($id)
    {
        $assignment = TaskAssignment::with([
            'committee.position',
            'jobResponsibility.position',
            'approver'
        ])->findOrFail($id);

        // Get committees for approver dropdown
        $committees = Committee::where('active_status', 'active')
            ->orderBy('full_name')
            ->get();

        // Get similar assignments
        $similarAssignments = TaskAssignment::with(['committee', 'jobResponsibility'])
            ->where(function ($q) use ($assignment) {
                $q->where('committee_id', $assignment->committee_id)
                    ->orWhere('job_responsibility_id', $assignment->job_responsibility_id);
            })
            ->where('id', '!=', $id)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('task-assignments.show', compact(
            'assignment',
            'committees',
            'similarAssignments'
        ));
    }

    /**
     * Show the form for editing the specified task assignment.
     */
    public function edit($id)
    {
        $assignment = TaskAssignment::findOrFail($id);

        $committees = Committee::where('active_status', 'active')
            ->orderBy('full_name')
            ->get();

        $jobResponsibilities = JobResponsibility::with('position')
            ->orderBy('task_name')
            ->get();

        $approvers = Committee::where('active_status', 'active')
            ->where('id', '!=', $assignment->committee_id)
            ->orderBy('full_name')
            ->get();

        return view('task-assignments.edit', compact(
            'assignment',
            'committees',
            'jobResponsibilities',
            'approvers'
        ));
    }

    /**
     * Update the specified task assignment in storage.
     */
    public function update(Request $request, $id)
    {
        $assignment = TaskAssignment::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'job_responsibility_id' => 'required|exists:job_responsibilities,id',
            'assigned_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:assigned_date',
            'status' => 'required|in:pending,in_progress,completed,overdue,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'completed_date' => 'nullable|date|after_or_equal:assigned_date',
            'approved_by' => 'nullable|exists:committees,id',
        ]);

        // Check if committee is active
        $committee = Committee::find($validated['committee_id']);
        if ($committee->active_status != 'active') {
            return back()->withInput()
                ->with('error', 'Tidak dapat mengubah penugasan ke pengurus yang tidak aktif.');
        }

        // Update status based on progress
        if (isset($validated['progress_percentage'])) {
            if ($validated['progress_percentage'] == 100 && $assignment->status != 'completed') {
                $validated['status'] = 'completed';
                $validated['completed_date'] = $validated['completed_date'] ?? now();
            } elseif ($validated['progress_percentage'] > 0 && $assignment->status == 'pending') {
                $validated['status'] = 'in_progress';
            }
        }

        // If marking as completed, set progress to 100 and completion date
        if ($validated['status'] == 'completed' && $assignment->status != 'completed') {
            $validated['progress_percentage'] = 100;
            $validated['completed_date'] = $validated['completed_date'] ?? now();
        }

        // Mark as overdue if due date passed
        if (isset($validated['due_date']) && Carbon::parse($validated['due_date'])->lt(today())) {
            if (!in_array($validated['status'], ['completed', 'cancelled'])) {
                $validated['status'] = 'overdue';
            }
        }

        // Set approved_at if approved_by is set
        if (isset($validated['approved_by']) && !$assignment->approved_by) {
            $validated['approved_at'] = now();
        }

        try {
            $assignment->update($validated);

            return redirect()->route('task-assignments.show', $assignment->id)
                ->with('success', 'Penugasan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui penugasan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified task assignment from storage.
     */
    public function destroy($id)
    {
        $assignment = TaskAssignment::findOrFail($id);

        // Cannot delete completed or approved assignments
        if ($assignment->status == 'completed') {
            return redirect()->route('task-assignments.index')
                ->with('error', 'Tidak dapat menghapus penugasan yang sudah selesai.');
        }

        if ($assignment->approved_by) {
            return redirect()->route('task-assignments.index')
                ->with('error', 'Tidak dapat menghapus penugasan yang sudah disetujui.');
        }

        try {
            $assignment->delete();
            return redirect()->route('task-assignments.index')
                ->with('success', 'Penugasan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('task-assignments.index')
                ->with('error', 'Gagal menghapus penugasan: ' . $e->getMessage());
        }
    }



    /**
     * Show overdue tasks.
     */
    public function overdue()
    {
        $overdueTasks = TaskAssignment::with(['committee.position', 'jobResponsibility'])
            ->where('due_date', '<', today())
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date')
            ->paginate(20);

        $committees = Committee::where('active_status', 'active')->get();
        $jobResponsibilities = JobResponsibility::all();

        return view('task-assignments.overdue', compact(
            'overdueTasks',
            'committees',
            'jobResponsibilities'
        ));
    }

    /**
     * Update task progress.
     */
    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $assignment = TaskAssignment::findOrFail($id);

        $oldProgress = $assignment->progress_percentage;
        $newProgress = $request->progress_percentage;

        $data = [
            'progress_percentage' => $newProgress,
        ];

        // Update notes if provided
        if ($request->has('notes')) {
            $data['notes'] = $request->notes;
        }

        // Update status based on progress
        if ($newProgress == 100 && $assignment->status != 'completed') {
            $data['status'] = 'completed';
            $data['completed_date'] = now();
        } elseif ($newProgress > 0 && $assignment->status == 'pending') {
            $data['status'] = 'in_progress';
        }

        // Check if overdue
        if ($assignment->due_date && Carbon::parse($assignment->due_date)->lt(today())) {
            if (!in_array($data['status'] ?? $assignment->status, ['completed', 'cancelled'])) {
                $data['status'] = 'overdue';
            }
        }

        try {
            $assignment->update($data);

            return back()->with(
                'success',
                "Progres tugas berhasil diubah dari {$oldProgress}% menjadi {$newProgress}%."
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah progres: ' . $e->getMessage());
        }
    }

    /**
     * Approve task assignment.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'approver_id' => 'required|exists:committees,id',
            'notes' => 'nullable|string',
        ]);

        $assignment = TaskAssignment::findOrFail($id);

        // Can only approve completed tasks
        if ($assignment->status != 'completed') {
            return back()->with('error', 'Hanya dapat menyetujui tugas yang sudah selesai.');
        }

        // Check if already approved
        if ($assignment->approved_by) {
            return back()->with('error', 'Tugas sudah disetujui sebelumnya.');
        }

        // Check if approver is active
        $approver = Committee::find($request->approver_id);
        if ($approver->active_status != 'active') {
            return back()->with('error', 'Hanya pengurus aktif yang dapat menyetujui tugas.');
        }

        try {
            $assignment->update([
                'approved_by' => $request->approver_id,
                'approved_at' => now(),
                'notes' => $request->notes ?? $assignment->notes,
            ]);

            return back()->with('success', 'Tugas berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui tugas: ' . $e->getMessage());
        }
    }

    /**
     * Show statistics for task assignments.
     */
    public function statistics()
    {
        // Overall statistics
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
            'pending_assignments' => TaskAssignment::where('status', 'pending')->count(),
            'in_progress_assignments' => TaskAssignment::where('status', 'in_progress')->count(),
        ];

        // Completion rate
        $totalCompleted = $stats['by_status']['completed'] ?? 0;
        $stats['completion_rate'] = $stats['total'] > 0 ? ($totalCompleted / $stats['total']) * 100 : 0;

        // Average completion time
        $avgCompletion = TaskAssignment::where('status', 'completed')
            ->whereNotNull('completed_date')
            ->whereNotNull('assigned_date')
            ->selectRaw('AVG(DATEDIFF(completed_date, assigned_date)) as avg_days')
            ->first();
        $stats['average_completion_time'] = $avgCompletion->avg_days ?? 0;

        // Approval rate
        $totalCompleted = $stats['by_status']['completed'] ?? 0;
        $totalApproved = TaskAssignment::where('status', 'completed')
            ->whereNotNull('approved_by')
            ->count();
        $stats['approval_rate'] = $totalCompleted > 0 ? ($totalApproved / $totalCompleted) * 100 : 0;

        // Top committees with most assignments
        $topCommittees = Committee::select('committees.id', 'committees.full_name')
            ->join('task_assignments', 'committees.id', '=', 'task_assignments.committee_id')
            ->selectRaw('count(task_assignments.id) as assignment_count')
            ->groupBy('committees.id', 'committees.full_name')
            ->orderBy('assignment_count', 'desc')
            ->limit(5)
            ->get();

        // Top responsibilities with most assignments
        $topResponsibilities = JobResponsibility::select('job_responsibilities.id', 'job_responsibilities.task_name')
            ->join('task_assignments', 'job_responsibilities.id', '=', 'task_assignments.job_responsibility_id')
            ->selectRaw('count(task_assignments.id) as assignment_count')
            ->groupBy('job_responsibilities.id', 'job_responsibilities.task_name')
            ->orderBy('assignment_count', 'desc')
            ->limit(5)
            ->get();

        return view('task-assignments.statistics', compact(
            'stats',
            'topCommittees',
            'topResponsibilities'
        ));
    }
}
