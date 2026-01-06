<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\OrganizationalStructure;
use Illuminate\Http\Request;

class OrganizationalStructureController extends Controller
{
    /**
     * Display organizational structure
     */
    public function index(Request $request)
    {
        try {
            $structures = OrganizationalStructure::with([
                'position',
                'children.position',
                'committees' => function ($query) {
                    $query->active()->select(['id', 'full_name', 'position_id']);
                }
            ])
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get();

            return ResponseHelper::success($request, $structures, 'Struktur organisasi berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil struktur organisasi', $e->getMessage(), 500);
        }
    }

    /**
     * Update organizational structure order
     */
    public function updateOrder(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|exists:organizational_structures,id',
                'items.*.order' => 'required|integer',
                'items.*.parent_id' => 'nullable|exists:organizational_structures,id',
            ]);

            foreach ($request->items as $item) {
                OrganizationalStructure::where('id', $item['id'])->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'] ?? null,
                ]);
            }

            return ResponseHelper::success($request, null, 'Urutan struktur berhasil diperbarui');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal memperbarui urutan struktur', $e->getMessage(), 500);
        }
    }
}
