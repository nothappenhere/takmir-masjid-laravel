<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Committee;
use App\Models\JobResponsibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PositionController extends Controller
{
    /**
     * Display a listing of the positions.
     */
    public function index(Request $request)
    {
        // Query with parent relationship and committee count
        $query = Position::with(['parent'])
            ->withCount(['committees', 'responsibilities', 'children'])
            ->latest();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        // Filter by level
        if ($request->has('level') && in_array($request->level, ['leadership', 'division_head', 'staff', 'volunteer'])) {
            $query->where('level', $request->level);
        }

        // Get all positions for parent dropdown
        $allPositions = Position::where('status', 'active')
            ->where('id', '!=', $request->id)
            ->get();

        // For tree view
        if ($request->has('view') && $request->view == 'tree') {
            $positions = Position::whereNull('parent_id')
                ->with(['children' => function ($q) {
                    $q->with(['children' => function ($q) {
                        $q->withCount('committees');
                    }])
                        ->withCount('committees');
                }])
                ->withCount('committees')
                ->orderBy('order')
                ->get();

            return view('positions.tree', compact('positions', 'allPositions'));
        }

        // Default: list view with pagination
        $positions = $query->paginate(15)->withQueryString();

        return view('positions.index', compact('positions', 'allPositions'));
    }

    /**
     * Show the form for creating a new position.
     */
    public function create()
    {
        $positions = Position::where('status', 'active')->get();
        return view('positions.create', compact('positions'));
    }

    /**
     * Store a newly created position in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:positions,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:positions,id',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'level' => 'required|in:leadership,division_head,staff,volunteer',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);

        // Set default order if not provided
        if (empty($validated['order'])) {
            $maxOrder = Position::max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;
        }

        // Prevent circular reference
        if ($validated['parent_id']) {
            if ($this->isCircularReference($validated['parent_id'])) {
                return back()->withInput()
                    ->with('error', 'Tidak dapat membuat referensi circular. Pilih parent yang berbeda.');
            }
        }

        try {
            $position = Position::create($validated);

            return redirect()->route('positions.show', $position->id)
                ->with('success', 'Jabatan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified position.
     */
    public function show($id)
    {
        $position = Position::with([
            'parent',
            'children' => function ($query) {
                $query->withCount('committees');
            },
            'committees' => function ($query) {
                $query->where('active_status', 'active')
                    ->with('currentPositionHistory');
            },
            'responsibilities' => function ($query) {
                $query->orderBy('priority')->orderBy('task_name');
            }
        ])
            ->withCount(['committees', 'responsibilities', 'children'])
            ->findOrFail($id);

        // Get committees statistics
        $committeeStats = [
            'total' => $position->committees()->count(),
            'active' => $position->committees()->where('active_status', 'active')->count(),
            'inactive' => $position->committees()->where('active_status', 'inactive')->count(),
            'resigned' => $position->committees()->where('active_status', 'resigned')->count(),
        ];

        // Get responsibility statistics
        $responsibilityStats = [
            'total' => $position->responsibilities()->count(),
            'core' => $position->responsibilities()->where('is_core_responsibility', true)->count(),
            'non_core' => $position->responsibilities()->where('is_core_responsibility', false)->count(),
            'by_priority' => $position->responsibilities()
                ->selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->get()
                ->pluck('count', 'priority'),
        ];

        return view('positions.show', compact(
            'position',
            'committeeStats',
            'responsibilityStats'
        ));
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit($id)
    {
        $position = Position::findOrFail($id);
        $positions = Position::where('status', 'active')
            ->where('id', '!=', $id)
            ->get();

        return view('positions.edit', compact('position', 'positions'));
    }

    /**
     * Update the specified position in storage.
     */
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:positions,name,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:positions,id',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'level' => 'required|in:leadership,division_head,staff,volunteer',
        ]);

        // Prevent circular reference
        if ($validated['parent_id']) {
            if ($validated['parent_id'] == $id) {
                return back()->withInput()
                    ->with('error', 'Jabatan tidak dapat menjadi parent dari dirinya sendiri.');
            }

            if ($this->isCircularReference($validated['parent_id'], $id)) {
                return back()->withInput()
                    ->with('error', 'Tidak dapat membuat referensi circular. Pilih parent yang berbeda.');
            }
        }

        // Update slug if name changed
        if ($position->name != $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        try {
            $position->update($validated);

            return redirect()->route('positions.show', $position->id)
                ->with('success', 'Jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified position from storage.
     */
    public function destroy($id)
    {
        $position = Position::findOrFail($id);

        // Check if position has committees
        if ($position->committees()->exists()) {
            return redirect()->route('positions.index')
                ->with('error', 'Tidak dapat menghapus jabatan yang masih memiliki pengurus.');
        }

        // Check if position has children
        if ($position->children()->exists()) {
            return redirect()->route('positions.index')
                ->with('error', 'Tidak dapat menghapus jabatan yang masih memiliki sub-jabatan.');
        }

        // Check if position has responsibilities
        if ($position->responsibilities()->exists()) {
            return redirect()->route('positions.index')
                ->with('error', 'Tidak dapat menghapus jabatan yang masih memiliki tugas.');
        }

        try {
            $position->delete();
            return redirect()->route('positions.index')
                ->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('positions.index')
                ->with('error', 'Gagal menghapus jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Show committees for a specific position.
     */
    public function committees($id)
    {
        $position = Position::findOrFail($id);
        $committees = $position->committees()
            ->with(['currentPositionHistory'])
            ->orderBy('full_name')
            ->paginate(15);

        return view('positions.committees', compact('position', 'committees'));
    }

    /**
     * Show responsibilities for a specific position.
     */
    public function responsibilities($id)
    {
        $position = Position::findOrFail($id);
        $responsibilities = $position->responsibilities()
            ->orderBy('priority')
            ->orderBy('task_name')
            ->paginate(15);

        return view('positions.responsibilities', compact('position', 'responsibilities'));
    }

    /**
     * Check for circular reference in position hierarchy.
     */
    private function isCircularReference($parentId, $currentId = null)
    {
        if (!$currentId) {
            return false;
        }

        $parent = Position::find($parentId);
        if (!$parent) {
            return false;
        }

        // Traverse up the hierarchy
        while ($parent) {
            if ($parent->id == $currentId) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Update position order (for drag & drop).
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:positions,id',
            'positions.*.order' => 'required|integer',
            'positions.*.parent_id' => 'nullable|exists:positions,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->positions as $item) {
                Position::where('id', $item['id'])->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Urutan jabatan berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get position hierarchy as JSON for tree view.
     */
    public function hierarchy()
    {
        $positions = Position::whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->with(['children' => function ($q) {
                    $q->with(['committees' => function ($q) {
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

        return response()->json($positions);
    }
}
