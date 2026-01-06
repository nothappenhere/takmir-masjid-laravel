@extends('layouts.app')

@section('title', 'Jadwal Tugas Pengurus')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-alt text-primary"></i> Jadwal Tugas: {{ $committee->full_name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('committees.index') }}">Pengurus</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('committees.show', $committee->id) }}">{{ $committee->full_name }}</a></li>
                    <li class="breadcrumb-item active">Jadwal Tugas</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('committees.show', $committee->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('duty-schedules.create') }}?committee_id={{ $committee->id }}"
               class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Jadwal
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $duties->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Selesai
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $committee->dutySchedules()->where('status', 'completed')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Sedang Berjalan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $committee->dutySchedules()->where('status', 'ongoing')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Jadwal Mendatang
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $committee->dutySchedules()->whereDate('duty_date', '>=', today())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Duties Table -->
    <div class="card shadow">
        <div class="card-body">
            @if($duties->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Lokasi</th>
                                <th>Tugas</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($duties as $duty)
                            <tr class="{{ $duty->duty_date < today() && $duty->status != 'completed' ? 'table-danger' : '' }}">
                                <td>{{ $loop->iteration + ($duties->currentPage() - 1) * $duties->perPage() }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($duty->duty_date)->format('d/m/Y') }}
                                    @if($duty->duty_date < today() && $duty->status != 'completed')
                                        <br><small class="text-danger">Terlambat</small>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($duty->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($duty->end_time)->format('H:i') }}
                                </td>
                                <td>{{ $duty->location }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $duty->duty_type }}</span>
                                </td>
                                <td>
                                    @if($duty->status == 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif($duty->status == 'ongoing')
                                        <span class="badge bg-warning">Sedang Berjalan</span>
                                    @elseif($duty->status == 'cancelled')
                                        <span class="badge bg-danger">Dibatalkan</span>
                                    @else
                                        <span class="badge bg-info">{{ $duty->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $duty->remarks ?? '-' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('duty-schedules.show', $duty->id) }}"
                                           class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($duty->status != 'completed' && $duty->status != 'cancelled')
                                            <button type="button" class="btn btn-sm btn-warning"
                                                    title="Update Status"
                                                    onclick="updateStatus({{ $duty->id }}, '{{ $duty->status }}')">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($duties->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Menampilkan {{ $duties->firstItem() }} - {{ $duties->lastItem() }} dari {{ $duties->total() }} jadwal
                    </div>
                    <div>
                        {{ $duties->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada jadwal tugas untuk pengurus ini</p>
                    <a href="{{ route('duty-schedules.create') }}?committee_id={{ $committee->id }}"
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Jadwal Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status Baru</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="pending">Pending</option>
                            <option value="ongoing">Sedang Berjalan</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Catatan</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStatus(id, currentStatus) {
    const form = document.getElementById('statusForm');
    form.action = `/duty-schedules/${id}/status/update`;

    const statusSelect = document.getElementById('status');
    statusSelect.value = currentStatus;

    new bootstrap.Modal(document.getElementById('statusModal')).show();
}
</script>
@endpush
