@extends('layouts.app')

@section('title', 'Tugas Terlambat')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-exclamation-triangle text-danger"></i> Tugas Terlambat
        </h1>
        <div class="btn-group">
            <a href="{{ route('task-assignments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('task-assignments.statistics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Statistik
            </a>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Tugas Terlambat
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overdueTasks->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Telat 1-3 Hari
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $count = 0;
                                    foreach($overdueTasks as $task) {
                                        $daysLate = now()->diffInDays($task->due_date, false) * -1;
                                        if ($daysLate >= 1 && $daysLate <= 3) $count++;
                                    }
                                @endphp
                                {{ $count }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Telat 4-7 Hari
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $count = 0;
                                    foreach($overdueTasks as $task) {
                                        $daysLate = now()->diffInDays($task->due_date, false) * -1;
                                        if ($daysLate >= 4 && $daysLate <= 7) $count++;
                                    }
                                @endphp
                                {{ $count }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Telat > 7 Hari
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $count = 0;
                                    foreach($overdueTasks as $task) {
                                        $daysLate = now()->diffInDays($task->due_date, false) * -1;
                                        if ($daysLate > 7) $count++;
                                    }
                                @endphp
                                {{ $count }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Tasks Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tugas</th>
                            <th>Pengurus</th>
                            <th>Deadline</th>
                            <th>Terlambat</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueTasks as $task)
                        @php
                            $daysLate = now()->diffInDays($task->due_date, false) * -1;
                            $bgClass = $daysLate > 7 ? 'bg-danger bg-opacity-10' : ($daysLate > 3 ? 'bg-warning bg-opacity-10' : 'bg-warning bg-opacity-5');
                        @endphp
                        <tr class="{{ $bgClass }}">
                            <td>{{ $loop->iteration + ($overdueTasks->currentPage() - 1) * $overdueTasks->perPage() }}</td>
                            <td>
                                <div class="fw-bold">{{ $task->jobResponsibility->task_name }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-sitemap"></i> {{ $task->jobResponsibility->position->name ?? '-' }}
                                </small>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $task->committee->full_name }}</div>
                                @if($task->committee->position)
                                    <small class="text-muted">{{ $task->committee->position->name }}</small>
                                @endif
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $daysLate > 7 ? 'danger' : 'warning' }}">
                                    {{ $daysLate }} hari
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress grow" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: {{ $task->progress_percentage }}%"></div>
                                    </div>
                                    <small class="ms-2">{{ $task->progress_percentage }}%</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger">Terlambat</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('task-assignments.show', $task->id) }}" 
                                       class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('task-assignments.edit', $task->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            title="Update Progress"
                                            onclick="updateProgress({{ $task->id }})">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="text-success">Tidak ada tugas yang terlambat</p>
                                <p class="text-muted">Selamat! Semua tugas berjalan sesuai jadwal.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($overdueTasks->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan {{ $overdueTasks->firstItem() }} - {{ $overdueTasks->lastItem() }} dari {{ $overdueTasks->total() }} data
                </div>
                <div>
                    {{ $overdueTasks->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Progress Update Modal -->
<div class="modal fade" id="progressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="progressForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_progress" class="form-label">Progress Saat Ini (%)</label>
                        <input type="range" class="form-range" 
                               id="modal_progress" name="progress_percentage" 
                               min="0" max="100" step="5" value="0"
                               oninput="updateModalProgressValue(this.value)">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">0%</small>
                            <span id="modalProgressValue" class="fw-bold">0%</span>
                            <small class="text-muted">100%</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modal_notes" class="form-label">Catatan Update</label>
                        <textarea class="form-control" id="modal_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateProgress(taskId) {
    const form = document.getElementById('progressForm');
    form.action = `/task-assignments/${taskId}/update-progress`;
    
    // Fetch current progress
    fetch(`/task-assignments/${taskId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modal_progress').value = data.progress_percentage || 0;
            updateModalProgressValue(data.progress_percentage || 0);
        });
    
    new bootstrap.Modal(document.getElementById('progressModal')).show();
}

function updateModalProgressValue(value) {
    document.getElementById('modalProgressValue').textContent = value + '%';
}
</script>
@endpush