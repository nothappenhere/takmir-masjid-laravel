<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Position;
use App\Models\DutySchedule;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommitteeController extends Controller
{
    /**
     * Display a listing of the committees.
     */
    public function index(Request $request)
    {
        // Query dengan eager loading
        $query = Committee::with(['position'])
            ->latest();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['active', 'inactive', 'resigned'])) {
            $query->where('active_status', $request->status);
        }

        // Filter by position
        if ($request->has('position_id') && $request->position_id != '') {
            $query->where('position_id', $request->position_id);
        }

        // Filter by gender
        if ($request->has('gender') && in_array($request->gender, ['male', 'female'])) {
            $query->where('gender', $request->gender);
        }

        // Get all positions for filter dropdown
        $positions = Position::where('status', 'active')->get();

        // Pagination
        $committees = $query->paginate(15)->withQueryString();

        return view('committees.index', compact('committees', 'positions'));
    }

    /**
     * Show the form for creating a new committee.
     */
    public function create()
    {
        $positions = Position::where('status', 'active')->get();
        return view('committees.create', compact('positions'));
    }

    /**
     * Store a newly created committee in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:200|unique:committees,email',
            'phone_number' => 'nullable|string|max:20|unique:committees,phone_number',
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'join_date' => 'nullable|date|after_or_equal:birth_date',
            'active_status' => 'required|in:active,inactive,resigned',
            'position_id' => 'nullable|exists:positions,id',
        ]);

        // Set join date to today if not provided
        if (empty($validated['join_date'])) {
            $validated['join_date'] = now()->toDateString();
        }

        try {
            DB::beginTransaction();

            // Create committee
            $committee = Committee::create($validated);

            // Create position history if position is assigned
            if ($committee->position_id) {
                $committee->positionHistories()->create([
                    'position_id' => $committee->position_id,
                    'start_date' => $committee->join_date ?? now(),
                    'is_active' => true,
                    'appointment_type' => 'permanent',
                ]);
            }

            DB::commit();

            return redirect()->route('committees.show', $committee->id)
                ->with('success', 'Data pengurus berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menambahkan data pengurus: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified committee.
     */
    public function show($id)
    {
        $committee = Committee::with([
            'position',
            'positionHistories' => function ($query) {
                $query->with('position')->orderBy('start_date', 'desc');
            },
            'dutySchedules' => function ($query) {
                $query->orderBy('duty_date', 'desc')->limit(10);
            },
            'taskAssignments' => function ($query) {
                $query->with('jobResponsibility')
                    ->orderBy('due_date')
                    ->limit(10);
            }
        ])->findOrFail($id);

        // Statistics for this committee
        $stats = [
            'total_duties' => $committee->dutySchedules()->count(),
            'completed_duties' => $committee->dutySchedules()->where('status', 'completed')->count(),
            'total_tasks' => $committee->taskAssignments()->count(),
            'completed_tasks' => $committee->taskAssignments()->where('status', 'completed')->count(),
            'overdue_tasks' => $committee->taskAssignments()->where('status', 'overdue')->count(),
        ];

        return view('committees.show', compact('committee', 'stats'));
    }

    /**
     * Show the form for editing the specified committee.
     */
    public function edit($id)
    {
        $committee = Committee::findOrFail($id);
        $positions = Position::where('status', 'active')->get();

        return view('committees.edit', compact('committee', 'positions'));
    }

    /**
     * Update the specified committee in storage.
     */
    public function update(Request $request, $id)
    {
        $committee = Committee::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:200|unique:committees,email,' . $id,
            'phone_number' => 'nullable|string|max:20|unique:committees,phone_number,' . $id,
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'join_date' => 'nullable|date|after_or_equal:birth_date',
            'active_status' => 'required|in:active,inactive,resigned',
            'position_id' => 'nullable|exists:positions,id',
        ]);

        try {
            DB::beginTransaction();

            $oldPositionId = $committee->position_id;
            $newPositionId = $validated['position_id'] ?? null;

            // Update committee
            $committee->update($validated);

            // Handle position history if position changed
            if ($oldPositionId != $newPositionId) {
                // Deactivate old position history if exists
                if ($oldPositionId) {
                    $committee->positionHistories()
                        ->where('position_id', $oldPositionId)
                        ->where('is_active', true)
                        ->update([
                            'end_date' => now(),
                            'is_active' => false
                        ]);
                }

                // Create new position history if new position assigned
                if ($newPositionId) {
                    $committee->positionHistories()->create([
                        'position_id' => $newPositionId,
                        'start_date' => now(),
                        'is_active' => true,
                        'appointment_type' => 'permanent',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('committees.show', $committee->id)
                ->with('success', 'Data pengurus berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui data pengurus: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified committee from storage.
     */
    public function destroy($id)
    {
        $committee = Committee::findOrFail($id);

        // Check if committee has ongoing duties
        $hasOngoingDuties = $committee->dutySchedules()
            ->where('status', 'ongoing')
            ->exists();

        if ($hasOngoingDuties) {
            return redirect()->route('committees.index')
                ->with('error', 'Tidak dapat menghapus pengurus yang sedang bertugas.');
        }

        // Check if committee has active tasks
        $hasActiveTasks = $committee->taskAssignments()
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if ($hasActiveTasks) {
            return redirect()->route('committees.index')
                ->with('error', 'Tidak dapat menghapus pengurus yang memiliki tugas aktif.');
        }

        try {
            $committee->delete();
            return redirect()->route('committees.index')
                ->with('success', 'Data pengurus berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('committees.index')
                ->with('error', 'Gagal menghapus data pengurus: ' . $e->getMessage());
        }
    }

    /**
     * Show duties for a specific committee.
     */
    public function duties($id)
    {
        $committee = Committee::findOrFail($id);
        $duties = $committee->dutySchedules()
            ->with(['committee'])
            ->orderBy('duty_date', 'desc')
            ->paginate(15);

        return view('committees.duties', compact('committee', 'duties'));
    }

    /**
     * Show tasks for a specific committee.
     */
    public function tasks($id)
    {
        $committee = Committee::findOrFail($id);
        $tasks = $committee->taskAssignments()
            ->with(['jobResponsibility'])
            ->orderBy('due_date')
            ->paginate(15);

        return view('committees.tasks', compact('committee', 'tasks'));
    }
}
