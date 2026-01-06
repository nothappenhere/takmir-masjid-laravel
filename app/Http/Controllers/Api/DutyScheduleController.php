<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDutyScheduleRequest;
use App\Http\Requests\UpdateDutyScheduleRequest;
use App\Models\DutySchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DutyScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = DutySchedule::with(['committee.position']);

            // Filters
            if ($request->filled('date')) {
                $query->whereDate('duty_date', $request->date);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('duty_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('duty_date', '<=', $request->date_to);
            }

            if ($request->filled('type')) {
                $query->where('duty_type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('committee_id')) {
                $query->where('committee_id', $request->committee_id);
            }

            if ($request->filled('location')) {
                $query->where('location', 'like', "%{$request->location}%");
            }

            // Default: show upcoming schedules
            if (!$request->filled('date') && !$request->filled('date_from')) {
                $query->whereDate('duty_date', '>=', today());
            }

            // Sorting
            $query->orderBy('duty_date')
                ->orderBy('start_time');

            // Pagination
            $perPage = $request->query('per_page', 30);
            $schedules = $query->paginate($perPage);

            return ResponseHelper::paginated($request, $schedules, 'Jadwal tugas berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil jadwal tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDutyScheduleRequest $request)
    {
        try {
            $data = $request->validated();

            // Check for schedule conflicts
            $conflict = DutySchedule::where('committee_id', $data['committee_id'])
                ->whereDate('duty_date', $data['duty_date'])
                ->where(function ($query) use ($data) {
                    $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                        ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                        ->orWhere(function ($q) use ($data) {
                            $q->where('start_time', '<=', $data['start_time'])
                                ->where('end_time', '>=', $data['end_time']);
                        });
                })
                ->exists();

            if ($conflict) {
                return ResponseHelper::error(
                    $request,
                    'Pengurus sudah memiliki jadwal pada waktu tersebut',
                    null,
                    409
                );
            }

            $schedule = DutySchedule::create($data);

            return ResponseHelper::success(
                $request,
                $schedule->load('committee'),
                'Jadwal tugas berhasil ditambahkan',
                201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menambahkan jadwal tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $schedule = DutySchedule::with(['committee.position'])->findOrFail($id);
            return ResponseHelper::success($request, $schedule, 'Detail jadwal berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jadwal tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil detail jadwal', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDutyScheduleRequest $request, $id)
    {
        try {
            $schedule = DutySchedule::findOrFail($id);
            $data = $request->validated();

            $schedule->update($data);

            return ResponseHelper::success(
                $request,
                $schedule->load('committee'),
                'Jadwal berhasil diperbarui'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jadwal tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal memperbarui jadwal', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $schedule = DutySchedule::findOrFail($id);

            // Cannot delete ongoing schedules
            if ($schedule->status === 'ongoing') {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menghapus jadwal yang sedang berlangsung',
                    null,
                    409
                );
            }

            $schedule->delete();

            return ResponseHelper::success($request, null, 'Jadwal berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jadwal tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menghapus jadwal', $e->getMessage(), 500);
        }
    }

    /**
     * Get today's schedule
     */
    public function today(Request $request)
    {
        try {
            $schedules = DutySchedule::with(['committee.position'])
                ->whereDate('duty_date', today())
                ->orderBy('start_time')
                ->get();

            return ResponseHelper::success($request, $schedules, 'Jadwal hari ini berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil jadwal hari ini', $e->getMessage(), 500);
        }
    }

    /**
     * Get weekly schedule
     */
    public function weekly(Request $request)
    {
        try {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            $schedules = DutySchedule::with(['committee.position'])
                ->whereBetween('duty_date', [$startOfWeek, $endOfWeek])
                ->orderBy('duty_date')
                ->orderBy('start_time')
                ->get()
                ->groupBy('duty_date');

            return ResponseHelper::success($request, $schedules, 'Jadwal mingguan berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil jadwal mingguan', $e->getMessage(), 500);
        }
    }

    /**
     * Update schedule status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,ongoing,completed,cancelled',
                'remarks' => 'nullable|string',
            ]);

            $schedule = DutySchedule::findOrFail($id);

            $oldStatus = $schedule->status;
            $newStatus = $request->status;

            // Update schedule
            $schedule->update([
                'status' => $newStatus,
                'remarks' => $request->remarks ?? $schedule->remarks,
            ]);

            return ResponseHelper::success(
                $request,
                $schedule,
                "Status jadwal berhasil diubah dari {$oldStatus} menjadi {$newStatus}"
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jadwal tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengubah status jadwal', $e->getMessage(), 500);
        }
    }
}
