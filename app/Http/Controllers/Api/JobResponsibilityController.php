<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobResponsibilityRequest;
use App\Http\Requests\UpdateJobResponsibilityRequest;
use App\Models\JobResponsibility;
use App\Models\Position;
use Illuminate\Http\Request;

class JobResponsibilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = JobResponsibility::with(['position'])
                ->withCount(['taskAssignments']);

            // Filters
            if ($request->filled('position_id')) {
                $query->where('position_id', $request->position_id);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('is_core')) {
                $query->where('is_core_responsibility', $request->boolean('is_core'));
            }

            if ($request->filled('frequency')) {
                $query->where('frequency', $request->frequency);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('task_name', 'like', "%{$search}%")
                        ->orWhere('task_description', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortField = $request->query('sort_by', 'priority');
            $sortDirection = $request->query('sort_dir', 'desc');

            if ($sortField === 'priority') {
                $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')");
            } else {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->query('per_page', 20);
            $responsibilities = $query->paginate($perPage);

            return ResponseHelper::paginated($request, $responsibilities, 'Daftar tugas berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil data tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobResponsibilityRequest $request)
    {
        try {
            $data = $request->validated();

            // Verify position exists and is active
            $position = Position::find($data['position_id']);
            if ($position && $position->status !== 'active') {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menambahkan tugas untuk jabatan yang tidak aktif',
                    null,
                    422
                );
            }

            $responsibility = JobResponsibility::create($data);

            return ResponseHelper::success(
                $request,
                $responsibility->load('position'),
                'Tugas berhasil ditambahkan',
                201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menambahkan tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $responsibility = JobResponsibility::with([
                'position',
                'taskAssignments' => function ($query) {
                    $query->with(['committee'])
                        ->orderBy('status')
                        ->orderBy('due_date');
                }
            ])
                ->withCount(['taskAssignments'])
                ->findOrFail($id);

            // Add active assignments count
            $responsibility->active_assignments_count = $responsibility->taskAssignments()
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            return ResponseHelper::success($request, $responsibility, 'Detail tugas berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Tugas tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil detail tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobResponsibilityRequest $request, $id)
    {
        try {
            $responsibility = JobResponsibility::findOrFail($id);
            $data = $request->validated();

            // If changing position, verify new position exists and is active
            if (isset($data['position_id']) && $data['position_id'] != $responsibility->position_id) {
                $position = Position::find($data['position_id']);
                if ($position && $position->status !== 'active') {
                    return ResponseHelper::error(
                        $request,
                        'Tidak dapat mengubah tugas ke jabatan yang tidak aktif',
                        null,
                        422
                    );
                }

                // Check if there are active assignments
                $activeAssignments = $responsibility->taskAssignments()
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->exists();

                if ($activeAssignments) {
                    return ResponseHelper::error(
                        $request,
                        'Tidak dapat mengubah jabatan karena ada penugasan yang aktif',
                        null,
                        409
                    );
                }
            }

            $responsibility->update($data);

            return ResponseHelper::success(
                $request,
                $responsibility->load('position'),
                'Tugas berhasil diperbarui'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Tugas tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal memperbarui tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $responsibility = JobResponsibility::findOrFail($id);

            // Check if there are any assignments
            if ($responsibility->taskAssignments()->exists()) {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menghapus tugas yang sudah memiliki penugasan',
                    null,
                    409
                );
            }

            $responsibility->delete();

            return ResponseHelper::success($request, null, 'Tugas berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Tugas tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menghapus tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Get responsibilities by position
     */
    public function byPosition(Request $request, $positionId)
    {
        try {
            $position = Position::findOrFail($positionId);

            $query = $position->responsibilities()
                ->withCount(['taskAssignments'])
                ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
                ->orderBy('task_name');

            // Filter core responsibilities only
            if ($request->boolean('core_only')) {
                $query->where('is_core_responsibility', true);
            }

            $perPage = $request->query('per_page', 20);
            $responsibilities = $query->paginate($perPage);

            return ResponseHelper::paginated($request, $responsibilities, 'Daftar tugas untuk jabatan ini');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Get statistics for responsibilities
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total' => JobResponsibility::count(),
                'by_priority' => JobResponsibility::selectRaw('priority, count(*) as count')
                    ->groupBy('priority')
                    ->get()
                    ->pluck('count', 'priority'),
                'by_frequency' => JobResponsibility::selectRaw('frequency, count(*) as count')
                    ->groupBy('frequency')
                    ->get()
                    ->pluck('count', 'frequency'),
                'core_responsibilities' => JobResponsibility::where('is_core_responsibility', true)->count(),
                'non_core_responsibilities' => JobResponsibility::where('is_core_responsibility', false)->count(),
                'positions_with_responsibilities' => JobResponsibility::distinct('position_id')->count('position_id'),
            ];

            return ResponseHelper::success($request, $stats, 'Statistik tugas berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil statistik', $e->getMessage(), 500);
        }
    }
}
