<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Position::with(['parent', 'children'])
                ->withCount(['committees', 'responsibilities']);

            // Filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            if ($request->filled('search')) {
                $query->where('name', 'like', "%{$request->search}%");
            }

            // Get tree structure if requested
            if ($request->boolean('tree')) {
                $positions = $query->whereNull('parent_id')
                    ->with(['children' => function ($q) {
                        $q->with(['children' => function ($q) {
                            $q->withCount('committees');
                        }])->withCount('committees');
                    }])
                    ->orderBy('order')
                    ->get();

                return ResponseHelper::success($request, $positions, 'Struktur jabatan berhasil diambil');
            }

            // Default: flat list with pagination
            $query->orderBy('order')->orderBy('name');
            $perPage = $request->query('per_page', 20);
            $positions = $query->paginate($perPage);

            return ResponseHelper::paginated($request, $positions, 'Daftar jabatan berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil data jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePositionRequest $request)
    {
        try {
            $data = $request->validated();

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $position = Position::create($data);

            return ResponseHelper::success(
                $request,
                $position->load(['parent', 'children']),
                'Jabatan berhasil ditambahkan',
                201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menambahkan jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $position = Position::with([
                'parent',
                'children',
                'committees' => function ($query) {
                    $query->active()->with('currentPositionHistory');
                },
                'responsibilities' => function ($query) {
                    $query->orderBy('priority')->orderBy('task_name');
                }
            ])->withCount(['committees', 'responsibilities'])->findOrFail($id);

            return ResponseHelper::success($request, $position, 'Detail jabatan berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil detail jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionRequest $request, $id)
    {
        try {
            $position = Position::findOrFail($id);
            $data = $request->validated();

            // Prevent circular reference
            if (isset($data['parent_id']) && $data['parent_id'] == $id) {
                return ResponseHelper::error($request, 'Jabatan tidak dapat menjadi parent dari dirinya sendiri', null, 422);
            }

            $position->update($data);

            return ResponseHelper::success(
                $request,
                $position->load(['parent', 'children']),
                'Jabatan berhasil diperbarui'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal memperbarui jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $position = Position::findOrFail($id);

            // Check if position has committees
            if ($position->committees()->exists()) {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menghapus jabatan yang masih memiliki pengurus',
                    null,
                    409
                );
            }

            // Check if position has children
            if ($position->children()->exists()) {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menghapus jabatan yang masih memiliki sub-jabatan',
                    null,
                    409
                );
            }

            $position->delete();

            return ResponseHelper::success($request, null, 'Jabatan berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menghapus jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Get committees for a position
     */
    public function committees(Request $request, $id)
    {
        try {
            $position = Position::findOrFail($id);
            $committees = $position->committees()
                ->with(['currentPositionHistory'])
                ->paginate($request->query('per_page', 20));

            return ResponseHelper::paginated($request, $committees, 'Daftar pengurus untuk jabatan ini');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil pengurus', $e->getMessage(), 500);
        }
    }

    /**
     * Get responsibilities for a position
     */
    public function responsibilities(Request $request, $id)
    {
        try {
            $position = Position::findOrFail($id);
            $responsibilities = $position->responsibilities()
                ->orderBy('priority')
                ->orderBy('task_name')
                ->paginate($request->query('per_page', 20));

            return ResponseHelper::paginated($request, $responsibilities, 'Daftar tugas untuk jabatan ini');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Get position hierarchy tree
     */
    public function hierarchy(Request $request)
    {
        try {
            $positions = Position::whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query->with(['children' => function ($query) {
                        $query->with(['committees' => function ($q) {
                            $q->active()->select(['id', 'full_name', 'position_id']);
                        }]);
                    }])
                        ->with(['committees' => function ($q) {
                            $q->active()->select(['id', 'full_name', 'position_id']);
                        }]);
                }])
                ->with(['committees' => function ($q) {
                    $q->active()->select(['id', 'full_name', 'position_id']);
                }])
                ->orderBy('order')
                ->get();

            return ResponseHelper::success($request, $positions, 'Struktur hierarki berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil struktur hierarki', $e->getMessage(), 500);
        }
    }
}
