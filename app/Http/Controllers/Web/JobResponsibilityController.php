<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JobResponsibility;
use App\Models\Position;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobResponsibilityController extends Controller
{
    /**
     * Display a listing of the job responsibilities.
     */
    public function index(Request $request)
    {
        // Query with position relationship and task count
        $query = JobResponsibility::with(['position'])
            ->withCount(['taskAssignments'])
            ->latest();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('task_name', 'like', "%{$search}%")
                    ->orWhere('task_description', 'like', "%{$search}%")
                    ->orWhereHas('position', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by position
        if ($request->has('position_id') && $request->position_id != '') {
            $query->where('position_id', $request->position_id);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority != '') {
            $query->where('priority', $request->priority);
        }

        // Filter by frequency
        if ($request->has('frequency') && $request->frequency != '') {
            $query->where('frequency', $request->frequency);
        }

        // Filter by core responsibility
        if ($request->has('is_core') && $request->is_core != '') {
            $query->where('is_core_responsibility', $request->boolean('is_core'));
        }

        // Get active positions for filter
        $positions = Position::where('status', 'active')
            ->orderBy('name')
            ->get();

        // Pagination
        $responsibilities = $query->paginate(20)->withQueryString();

        return view('job-responsibilities.index', compact('responsibilities', 'positions'));
    }

    /**
     * Show the form for creating a new job responsibility.
     */
    public function create()
    {
        $positions = Position::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('job-responsibilities.create', compact('positions'));
    }

    /**
     * Store a newly created job responsibility in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'task_name' => 'required|string|max:200',
            'task_description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'estimated_hours' => 'nullable|integer|min:0|max:100',
            'frequency' => 'required|in:daily,weekly,monthly,yearly,as_needed',
            'is_core_responsibility' => 'boolean',
        ]);

        // Check if position is active
        $position = Position::find($validated['position_id']);
        if ($position->status != 'active') {
            return back()->withInput()
                ->with('error', 'Tidak dapat membuat tugas untuk jabatan yang tidak aktif.');
        }

        // Set default for is_core_responsibility
        $validated['is_core_responsibility'] = $validated['is_core_responsibility'] ?? true;

        try {
            $responsibility = JobResponsibility::create($validated);

            return redirect()->route('job-responsibilities.show', $responsibility->id)
                ->with('success', 'Tugas berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan tugas: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified job responsibility.
     */
    public function show($id)
    {
        $responsibility = JobResponsibility::with([
            'position',
            'taskAssignments' => function ($query) {
                $query->with(['committee'])
                    ->orderBy('status')
                    ->orderBy('due_date');
            }
        ])->withCount(['taskAssignments'])->findOrFail($id);

        // Get statistics
        $stats = [
            'total_assignments' => $responsibility->taskAssignments()->count(),
            'active_assignments' => $responsibility->taskAssignments()
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'completed_assignments' => $responsibility->taskAssignments()
                ->where('status', 'completed')
                ->count(),
            'overdue_assignments' => $responsibility->taskAssignments()
                ->where('status', 'overdue')
                ->count(),
        ];

        // Get recent assignments
        $recentAssignments = $responsibility->taskAssignments()
            ->with(['committee'])
            ->latest()
            ->limit(5)
            ->get();

        return view('job-responsibilities.show', compact(
            'responsibility',
            'stats',
            'recentAssignments'
        ));
    }

    /**
     * Show the form for editing the specified job responsibility.
     */
    public function edit($id)
    {
        $responsibility = JobResponsibility::findOrFail($id);
        $positions = Position::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('job-responsibilities.edit', compact('responsibility', 'positions'));
    }

    /**
     * Update the specified job responsibility in storage.
     */
    public function update(Request $request, $id)
    {
        $responsibility = JobResponsibility::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'task_name' => 'required|string|max:200',
            'task_description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'estimated_hours' => 'nullable|integer|min:0|max:100',
            'frequency' => 'required|in:daily,weekly,monthly,yearly,as_needed',
            'is_core_responsibility' => 'boolean',
        ]);

        // Check if position is active
        $position = Position::find($validated['position_id']);
        if ($position->status != 'active') {
            return back()->withInput()
                ->with('error', 'Tidak dapat mengubah tugas ke jabatan yang tidak aktif.');
        }

        // If changing position, check for active assignments
        if ($responsibility->position_id != $validated['position_id']) {
            $activeAssignments = $responsibility->taskAssignments()
                ->whereIn('status', ['pending', 'in_progress'])
                ->exists();

            if ($activeAssignments) {
                return back()->withInput()
                    ->with('error', 'Tidak dapat mengubah jabatan karena ada penugasan yang aktif.');
            }
        }

        // Set default for is_core_responsibility
        $validated['is_core_responsibility'] = $validated['is_core_responsibility'] ?? true;

        try {
            $responsibility->update($validated);

            return redirect()->route('job-responsibilities.show', $responsibility->id)
                ->with('success', 'Tugas berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui tugas: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified job responsibility from storage.
     */
    public function destroy($id)
    {
        $responsibility = JobResponsibility::findOrFail($id);

        // Check if responsibility has task assignments
        if ($responsibility->taskAssignments()->exists()) {
            return redirect()->route('job-responsibilities.index')
                ->with('error', 'Tidak dapat menghapus tugas yang masih memiliki penugasan.');
        }

        try {
            $responsibility->delete();
            return redirect()->route('job-responsibilities.index')
                ->with('success', 'Tugas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('job-responsibilities.index')
                ->with('error', 'Gagal menghapus tugas: ' . $e->getMessage());
        }
    }

    /**
     * Get responsibilities by position.
     */
    public function byPosition(Request $request, $positionId)
    {
        $position = Position::findOrFail($positionId);

        $responsibilities = JobResponsibility::where('position_id', $positionId)
            ->withCount(['taskAssignments'])
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->orderBy('task_name')
            ->paginate(20);

        return view('job-responsibilities.by-position', compact('position', 'responsibilities'));
    }

    /**
     * Show statistics for job responsibilities.
     */
    public function statistics()
    {
        $stats = [
            'total' => JobResponsibility::count(),
            'by_priority' => JobResponsibility::selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
                ->get(),
            'by_frequency' => JobResponsibility::selectRaw('frequency, count(*) as count')
                ->groupBy('frequency')
                ->orderBy('frequency')
                ->get(),
            'core_vs_non_core' => JobResponsibility::selectRaw('is_core_responsibility, count(*) as count')
                ->groupBy('is_core_responsibility')
                ->get()
                ->pluck('count', 'is_core_responsibility'),
            'positions_with_responsibilities' => JobResponsibility::distinct('position_id')->count('position_id'),
            'average_hours' => JobResponsibility::avg('estimated_hours'),
        ];

        // Get top 5 positions with most responsibilities
        $topPositions = Position::select('positions.id', 'positions.name')
            ->join('job_responsibilities', 'positions.id', '=', 'job_responsibilities.position_id')
            ->selectRaw('count(job_responsibilities.id) as responsibility_count')
            ->groupBy('positions.id', 'positions.name')
            ->orderBy('responsibility_count', 'desc')
            ->limit(5)
            ->get();

        return view('job-responsibilities.statistics', compact('stats', 'topPositions'));
    }
}
