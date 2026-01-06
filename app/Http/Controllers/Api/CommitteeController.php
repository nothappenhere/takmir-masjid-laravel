<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommitteeRequest;
use App\Http\Requests\UpdateCommitteeRequest;
use App\Models\Committee;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Committee::with(['position', 'currentPositionHistory'])
                ->withCount(['dutySchedules', 'taskAssignments']);

            // Filters
            if ($request->filled('status')) {
                $query->where('active_status', $request->status);
            }

            if ($request->filled('position_id')) {
                $query->where('position_id', $request->position_id);
            }

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortField = $request->query('sort_by', 'created_at');
            $sortDirection = $request->query('sort_dir', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->query('per_page', 20);
            $committees = $query->paginate($perPage);

            return ResponseHelper::paginated($request, $committees, 'Daftar pengurus berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil data pengurus', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommitteeRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('cv')) {
                $path = $request->file('cv')->store('cv', 'public');
                $data['cv'] = $path;
            }

            // Generate join date if not provided
            if (empty($data['join_date'])) {
                $data['join_date'] = now()->toDateString();
            }

            $committee = Committee::create($data);

            // Create position history if position is assigned
            if ($committee->position_id) {
                $committee->positionHistories()->create([
                    'position_id' => $committee->position_id,
                    'start_date' => now(),
                    'is_active' => true,
                    'appointment_type' => 'permanent',
                ]);
            }

            return ResponseHelper::success(
                $request,
                $committee->load(['position', 'currentPositionHistory']),
                'Pengurus berhasil ditambahkan',
                201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menambahkan pengurus', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $committee = Committee::with([
                'position',
                'positionHistories' => function ($query) {
                    $query->orderBy('start_date', 'desc');
                },
                'dutySchedules' => function ($query) {
                    $query->whereDate('duty_date', '>=', now())
                        ->orderBy('duty_date');
                },
                'taskAssignments' => function ($query) {
                    $query->whereIn('status', ['pending', 'in_progress'])
                        ->with('jobResponsibility')
                        ->orderBy('due_date');
                }
            ])->withCount(['dutySchedules', 'taskAssignments'])->findOrFail($id);

            return ResponseHelper::success(
                $request,
                $committee,
                'Detail pengurus berhasil diambil'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Pengurus tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil detail pengurus', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommitteeRequest $request, $id)
    {
        try {
            $committee = Committee::findOrFail($id);
            $data = $request->validated();

            // Update position history if position changed
            $oldPositionId = $committee->position_id;
            $newPositionId = $data['position_id'] ?? $oldPositionId;

            if ($oldPositionId != $newPositionId) {
                // Deactivate old position history
                if ($oldPositionId) {
                    $committee->positionHistories()
                        ->where('position_id', $oldPositionId)
                        ->where('is_active', true)
                        ->update([
                            'end_date' => now(),
                            'is_active' => false
                        ]);
                }

                // Create new position history
                if ($newPositionId) {
                    $committee->positionHistories()->create([
                        'position_id' => $newPositionId,
                        'start_date' => now(),
                        'is_active' => true,
                        'appointment_type' => 'permanent',
                    ]);
                }
            }

            $committee->update($data);

            return ResponseHelper::success(
                $request,
                $committee->load(['position', 'currentPositionHistory']),
                'Pengurus berhasil diperbarui'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Pengurus tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal memperbarui pengurus', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $committee = Committee::findOrFail($id);

            // Check if committee has active duties
            $activeDuties = $committee->dutySchedules()
                ->where('status', 'ongoing')
                ->exists();

            if ($activeDuties) {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menghapus pengurus yang sedang bertugas',
                    null,
                    409
                );
            }

            // Soft delete
            $committee->delete();

            return ResponseHelper::success(
                $request,
                null,
                'Pengurus berhasil dihapus'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Pengurus tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menghapus pengurus', $e->getMessage(), 500);
        }
    }

    /**
     * Get committees statistics
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total' => Committee::count(),
                'active' => Committee::where('active_status', 'active')->count(),
                'inactive' => Committee::where('active_status', 'inactive')->count(),
                'resigned' => Committee::where('active_status', 'resigned')->count(),
                'by_gender' => Committee::selectRaw('gender, count(*) as count')
                    ->groupBy('gender')
                    ->get()
                    ->pluck('count', 'gender'),
                'by_position_level' => Committee::join('positions', 'committees.position_id', '=', 'positions.id')
                    ->selectRaw('positions.level, count(*) as count')
                    ->groupBy('positions.level')
                    ->get()
                    ->pluck('count', 'level'),
            ];

            return ResponseHelper::success($request, $stats, 'Statistik pengurus berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil statistik', $e->getMessage(), 500);
        }
    }
}
