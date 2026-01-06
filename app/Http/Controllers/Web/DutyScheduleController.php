<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DutySchedule;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DutyScheduleController extends Controller
{
    /**
     * Display a listing of the duty schedules.
     */
    public function index(Request $request)
    {
        // Query with committee relationship
        $query = DutySchedule::with(['committee.position'])
            ->latest('duty_date')
            ->latest('start_time');

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('location', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas('committee', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by date
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('duty_date', $request->date);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('duty_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('duty_date', '<=', $request->date_to);
        }

        // Filter by committee
        if ($request->has('committee_id') && $request->committee_id != '') {
            $query->where('committee_id', $request->committee_id);
        }

        // Filter by duty type
        if ($request->has('duty_type') && $request->duty_type != '') {
            $query->where('duty_type', $request->duty_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by location - PERBAIKAN DI SINI
        if ($request->has('location') && $request->location != '') {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Get active committees for filter
        $committees = Committee::where('active_status', 'active')
            ->orderBy('full_name')
            ->get();

        // Duty types for filter
        $dutyTypes = DutySchedule::select('duty_type')
            ->distinct()
            ->whereNotNull('duty_type')
            ->where('duty_type', '!=', '')
            ->pluck('duty_type');

        // Locations for filter
        $locations = DutySchedule::select('location')
            ->distinct()
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->pluck('location');

        // Pagination
        $schedules = $query->paginate(20)->withQueryString();

        return view('duty-schedules.index', compact(
            'schedules',
            'committees',
            'dutyTypes',
            'locations'
        ));
    }

    /**
     * Show the form for creating a new duty schedule.
     */
    public function create()
    {
        $committees = Committee::where('active_status', 'active')
            ->orderBy('full_name')
            ->get();

        // Default values
        $defaultDate = now()->format('Y-m-d');
        $defaultStartTime = '08:00';
        $defaultEndTime = '12:00';

        return view('duty-schedules.create', compact(
            'committees',
            'defaultDate',
            'defaultStartTime',
            'defaultEndTime'
        ));
    }

    /**
     * Store a newly created duty schedule in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'duty_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:200',
            'duty_type' => 'required|in:piket,kebersihan,keamanan,administrasi,lainnya',
            'status' => 'required|in:pending,ongoing,completed,cancelled',
            'remarks' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly',
            'recurring_end_date' => 'nullable|date|after:duty_date',
        ]);

        // Check for schedule conflicts
        $conflict = DutySchedule::where('committee_id', $validated['committee_id'])
            ->whereDate('duty_date', $validated['duty_date'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()
                ->with('error', 'Pengurus sudah memiliki jadwal pada waktu tersebut.');
        }

        // Check if committee is active
        $committee = Committee::find($validated['committee_id']);
        if ($committee->active_status != 'active') {
            return back()->withInput()
                ->with('error', 'Tidak dapat membuat jadwal untuk pengurus yang tidak aktif.');
        }

        try {
            $schedule = DutySchedule::create($validated);

            // Create recurring schedules if needed
            if ($validated['is_recurring'] ?? false) {
                $this->createRecurringSchedules($schedule);
            }

            return redirect()->route('duty-schedules.show', $schedule->id)
                ->with('success', 'Jadwal berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified duty schedule.
     */
    public function show($id)
    {
        $schedule = DutySchedule::with(['committee.position'])->findOrFail($id);

        // Get similar schedules (same committee, same type)
        $similarSchedules = DutySchedule::where('committee_id', $schedule->committee_id)
            ->where('duty_type', $schedule->duty_type)
            ->where('id', '!=', $id)
            ->orderBy('duty_date', 'desc')
            ->limit(5)
            ->get();

        return view('duty-schedules.show', compact('schedule', 'similarSchedules'));
    }

    /**
     * Show the form for editing the specified duty schedule.
     */
    public function edit($id)
    {
        $schedule = DutySchedule::findOrFail($id);
        $committees = Committee::where('active_status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('duty-schedules.edit', compact('schedule', 'committees'));
    }

    /**
     * Update the specified duty schedule in storage.
     */
    public function update(Request $request, $id)
    {
        $schedule = DutySchedule::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'duty_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:200',
            'duty_type' => 'required|in:piket,kebersihan,keamanan,administrasi,lainnya',
            'status' => 'required|in:pending,ongoing,completed,cancelled',
            'remarks' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly',
            'recurring_end_date' => 'nullable|date|after:duty_date',
        ]);

        // Check for schedule conflicts (excluding current schedule)
        $conflict = DutySchedule::where('committee_id', $validated['committee_id'])
            ->whereDate('duty_date', $validated['duty_date'])
            ->where('id', '!=', $id)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()
                ->with('error', 'Pengurus sudah memiliki jadwal lain pada waktu tersebut.');
        }

        try {
            $schedule->update($validated);

            return redirect()->route('duty-schedules.show', $schedule->id)
                ->with('success', 'Jadwal berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified duty schedule from storage.
     */
    public function destroy($id)
    {
        $schedule = DutySchedule::findOrFail($id);

        // Cannot delete ongoing schedules
        if ($schedule->status == 'ongoing') {
            return redirect()->route('duty-schedules.index')
                ->with('error', 'Tidak dapat menghapus jadwal yang sedang berlangsung.');
        }

        try {
            $schedule->delete();
            return redirect()->route('duty-schedules.index')
                ->with('success', 'Jadwal berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('duty-schedules.index')
                ->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Show today's schedules.
     */
    public function today()
    {
        $today = now()->format('Y-m-d');

        $schedules = DutySchedule::with(['committee.position'])
            ->whereDate('duty_date', $today)
            ->orderBy('start_time')
            ->paginate(20);

        $committees = Committee::where('active_status', 'active')->get();

        return view('duty-schedules.today', compact('schedules', 'today', 'committees'));
    }

    /**
     * Show weekly schedules.
     */
    public function weekly()
    {
        $today = now();
        $weekStart = $today->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        $schedules = DutySchedule::with(['committee.position'])
            ->whereBetween('duty_date', [$weekStart, $weekEnd])
            ->orderBy('duty_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->duty_date)->format('Y-m-d');
            });

        $committees = Committee::where('active_status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('duty-schedules.weekly', compact('schedules', 'weekStart', 'weekEnd', 'committees'));
    }

    /**
     * Show upcoming schedules.
     */
    public function upcoming()
    {
        $today = now();
        $nextWeek = $today->copy()->addDays(7);

        $schedules = DutySchedule::with(['committee.position'])
            ->whereBetween('duty_date', [$today, $nextWeek])
            ->orderBy('duty_date')
            ->orderBy('start_time')
            ->paginate(20);

        // Group by date for better display
        $groupedSchedules = $schedules->groupBy(function ($item) {
            return Carbon::parse($item->duty_date)->format('Y-m-d');
        });

        $committees = Committee::where('active_status', 'active')->get();

        return view('duty-schedules.upcoming', compact('schedules', 'groupedSchedules', 'committees'));
    }

    /**
     * Update schedule status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,ongoing,completed,cancelled',
            'remarks' => 'nullable|string',
        ]);

        $schedule = DutySchedule::findOrFail($id);

        $oldStatus = $schedule->status;
        $newStatus = $request->status;

        // Validate status transition
        $validTransitions = [
            'pending' => ['ongoing', 'cancelled'],
            'ongoing' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $validTransitions[$oldStatus])) {
            return back()->with('error', "Tidak dapat mengubah status dari {$oldStatus} ke {$newStatus}.");
        }

        try {
            $schedule->update([
                'status' => $newStatus,
                'remarks' => $request->remarks ?? $schedule->remarks,
            ]);

            return back()->with('success', "Status berhasil diubah dari {$oldStatus} menjadi {$newStatus}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Create recurring schedules based on original schedule.
     */
    private function createRecurringSchedules(DutySchedule $originalSchedule)
    {
        if (!$originalSchedule->is_recurring || !$originalSchedule->recurring_type || !$originalSchedule->recurring_end_date) {
            return;
        }

        $startDate = Carbon::parse($originalSchedule->duty_date);
        $endDate = Carbon::parse($originalSchedule->recurring_end_date);
        $interval = 1; // days/weeks/months

        $schedules = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($currentDate->gt($startDate)) { // Skip original date
                $schedules[] = [
                    'committee_id' => $originalSchedule->committee_id,
                    'duty_date' => $currentDate->format('Y-m-d'),
                    'start_time' => $originalSchedule->start_time,
                    'end_time' => $originalSchedule->end_time,
                    'location' => $originalSchedule->location,
                    'duty_type' => $originalSchedule->duty_type,
                    'status' => 'pending',
                    'remarks' => $originalSchedule->remarks . ' (Recurring)',
                    'is_recurring' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Increment date based on recurring type
            switch ($originalSchedule->recurring_type) {
                case 'daily':
                    $currentDate->addDays($interval);
                    break;
                case 'weekly':
                    $currentDate->addWeeks($interval);
                    break;
                case 'monthly':
                    $currentDate->addMonths($interval);
                    break;
            }
        }

        // Insert in batches
        if (!empty($schedules)) {
            DutySchedule::insert($schedules);
        }
    }
}
