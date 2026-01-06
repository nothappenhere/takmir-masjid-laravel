<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionHistoryRequest;
use App\Http\Requests\UpdatePositionHistoryRequest;
use App\Models\Committee;
use App\Models\PositionHistory;
use Illuminate\Http\Request;

class PositionHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = PositionHistory::with(['committee', 'position'])
                ->latest();

            // Filters
            if ($request->filled('committee_id')) {
                $query->where('committee_id', $request->committee_id);
            }

            if ($request->filled('position_id')) {
                $query->where('position_id', $request->position_id);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('appointment_type')) {
                $query->where('appointment_type', $request->appointment_type);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('start_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('start_date', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('committee', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->query('per_page', 20);
            $histories = $query->paginate($perPage);

            return ResponseHelper::paginated($request, $histories, 'Daftar riwayat jabatan berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil data riwayat jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePositionHistoryRequest $request)
    {
        try {
            $data = $request->validated();

            // Check if committee exists and is active
            $committee = Committee::find($data['committee_id']);
            if (!$committee) {
                return ResponseHelper::notFound($request, 'Pengurus tidak ditemukan');
            }

            if ($committee->active_status !== 'active') {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menambahkan riwayat untuk pengurus yang tidak aktif',
                    null,
                    422
                );
            }

            // Deactivate other active histories for this committee
            if ($data['is_active'] ?? false) {
                PositionHistory::where('committee_id', $data['committee_id'])
                    ->where('is_active', true)
                    ->update([
                        // 'end_date' => now(),
                        'is_active' => false,
                    ]);

                // Update committee's current position
                $committee->update([
                    'position_id' => $data['position_id']
                ]);
            }

            $history = PositionHistory::create($data);

            return ResponseHelper::success(
                $request,
                $history->load(['committee', 'position']),
                'Riwayat jabatan berhasil ditambahkan',
                201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menambahkan riwayat jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $history = PositionHistory::with(['committee', 'position'])->findOrFail($id);
            return ResponseHelper::success($request, $history, 'Detail riwayat jabatan berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Riwayat jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil detail riwayat', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionHistoryRequest $request, $id)
    {
        try {
            $history = PositionHistory::findOrFail($id);
            $data = $request->validated();

            // If activating this history, deactivate others
            if (isset($data['is_active']) && $data['is_active'] && !$history->is_active) {
                PositionHistory::where('committee_id', $history->committee_id)
                    ->where('id', '!=', $id)
                    ->where('is_active', true)
                    ->update([
                        // 'end_date' => now(),
                        'is_active' => false,
                    ]);

                // Update committee's current position
                if (isset($data['position_id'])) {
                    $committee = Committee::find($history->committee_id);
                    $committee->update(['position_id' => $data['position_id']]);
                }
            }

            // If deactivating and no end date provided, set to today
            // if (isset($data['is_active']) && !$data['is_active'] && !isset($data['end_date'])) {
            //     $data['end_date'] = now();
            // }

            $history->update($data);

            return ResponseHelper::success(
                $request,
                $history->load(['committee', 'position']),
                'Riwayat jabatan berhasil diperbarui'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Riwayat jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal memperbarui riwayat jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $history = PositionHistory::findOrFail($id);

            // Cannot delete active history
            if ($history->is_active) {
                return ResponseHelper::error(
                    $request,
                    'Tidak dapat menghapus riwayat jabatan yang aktif',
                    null,
                    409
                );
            }

            $history->delete();

            return ResponseHelper::success($request, null, 'Riwayat jabatan berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Riwayat jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal menghapus riwayat jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Get position history for a committee
     */
    public function byCommittee(Request $request, $committeeId)
    {
        try {
            $committee = Committee::findOrFail($committeeId);

            $histories = PositionHistory::with(['position'])
                ->where('committee_id', $committeeId)
                ->orderBy('start_date', 'desc')
                ->paginate($request->query('per_page', 20));

            return ResponseHelper::paginated($request, $histories, 'Riwayat jabatan pengurus berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Pengurus tidak ditemukan');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil riwayat jabatan', $e->getMessage(), 500);
        }
    }

    /**
     * Get active position histories
     */
    public function active(Request $request)
    {
        try {
            $histories = PositionHistory::with(['committee', 'position'])
                ->where('is_active', true)
                ->whereHas('committee', function ($query) {
                    $query->where('active_status', 'active');
                })
                ->orderBy('start_date', 'desc')
                ->paginate($request->query('per_page', 20));

            return ResponseHelper::paginated($request, $histories, 'Riwayat jabatan aktif berhasil diambil');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengambil riwayat aktif', $e->getMessage(), 500);
        }
    }

    /**
     * End active position for a committee
     */
    public function endActivePosition(Request $request, $committeeId)
    {
        try {
            $request->validate([
                'end_date' => 'required|date|after_or_equal:today',
                'remarks' => 'nullable|string',
            ]);

            $activeHistory = PositionHistory::where('committee_id', $committeeId)
                ->where('is_active', true)
                ->firstOrFail();

            $activeHistory->update([
                'end_date' => $request->end_date,
                'is_active' => false,
                'remarks' => $request->remarks ?? $activeHistory->remarks,
            ]);

            // Update committee's position to null
            $committee = Committee::find($committeeId);
            $committee->update(['position_id' => null]);

            return ResponseHelper::success(
                $request,
                $activeHistory->load(['committee', 'position']),
                'Jabatan aktif berhasil diakhiri'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound($request, 'Tidak ada jabatan aktif untuk pengurus ini');
        } catch (\Exception $e) {
            return ResponseHelper::error($request, 'Gagal mengakhiri jabatan', $e->getMessage(), 500);
        }
    }
}
