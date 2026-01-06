@extends('layouts.app')

@section('title', 'Penugasan Pengurus')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tasks text-primary"></i> Penugasan: {{ $committee->full_name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('committees.index') }}">Pengurus</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('committees.show', $committee->id) }}">{{ $committee->full_name }}</a></li>
                    <li class="breadcrumb-item active">Penugasan</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('committees.show', $committee->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('task-assignments.create') }}?committee_id={{ $committee->id }}"
               class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Penugasan
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
                                Total Penugasan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $tasks->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
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
                                {{ $committee->taskAssignments()->where('status', 'completed')->count() }}
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
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Terlambat
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $committee->taskAssignments()->where('status', 'overdue')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Dalam Proses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $committee->taskAssignments()->where('status', 'in_progress')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card shadow">
        <div class="card-body">
            @if($tasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tugas</th>
                                <th>Tanggal</th>
                                <th>Deadline</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Disetujui</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                            <tr class="{{ $task->status == 'overdue' ? 'table-danger' : '' }}">
                                <td>{{ $loop->iteration + ($tasks->currentPage() - 1) * $tasks->perPage() }}</td>
                                <td>
                                    <div class="fw-bold">{{ $task->jobResponsibility->task_name ?? 'Tugas' }}</div>
                                    <small class="text-muted">{{ $task->notes }}</small>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($task->assigned_date)->format('d/m/Y') }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                    @if($task->status == 'overdue')
                                        <br><small class="text-danger">Terlambat {{ $task->due_date->diffInDays(now()) }} hari</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $task->progress_percentage == 100 ? 'success' : 'info' }}"
                                             style="width: {{ $task->progress_percentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $task->progress_percentage }}%</small>
                                </td>
                                <td>
                                    @if($task->status == 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif($task->status == 'overdue')
                                        <span class="badge bg-danger">Terlambat</span>
                                    @elseif($task->status == 'in_progress')
                                        <span class="badge bg-warning">Dalam Proses</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $task->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->approved_by)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Disetujui
                                        </span>
                                    @elseif($task->status == 'completed')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock"></i> Menunggu
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-minus"></i> -
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('task-assignments.show', $task->id) }}"
                                           class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($task->status != 'completed' && $task->status != 'cancelled')
                                            <button type="button" class="btn btn-sm btn-warning"
                                                    title="Update Progress"
                                                    onclick="updateProgress({{ $task->id }}, {{ $task->progress_percentage }})">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
                                        @endif
                                        @if($task->status == 'completed' && !$task->approved_by)
                                            <button type="button" class="btn btn-sm btn-success"
                                                    title="Setujui Tugas"
                                                    onclick="approveTask({{ $task->id }})">
                                                <i class="fas fa-check"></i>
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
                @if($tasks->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Menampilkan {{ $tasks->firstItem() }} - {{ $tasks->lastItem() }} dari {{ $tasks->total() }} penugasan
                    </div>
                    <div>
                        {{ $tasks->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada penugasan untuk pengurus ini</p>
                    <a href="{{ route('task-assignments.create') }}?committee_id={{ $committee->id }}"
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Penugasan Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Update Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Progress Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="progressForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="progress_percentage" class="form-label">Progress (%)</label>
                        <input type="range" class="form-range" id="progress_range"
                               min="0" max="100" step="5"
                               oninput="document.getElementById('progress_value').textContent = this.value + '%'">
                        <div class="d-flex justify-content-between">
                            <span>0%</span>
                            <span id="progress_value" class="fw-bold">50%</span>
                            <span>100%</span>
                        </div>
                        <input type="hidden" id="progress_percentage" name="progress_percentage">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update Progress</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Task Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setujui Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approver_id" class="form-label">Disetujui Oleh</label>
                        <select class="form-select" id="approver_id" name="approver_id" required>
                            <option value="">Pilih Penyetuju</option>
                            @foreach(App\Models\Committee::where('active_status', 'active')->get() as $approver)
                                <option value="{{ $approver->id }}">{{ $approver->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Persetujuan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Setujui Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateProgress(id, currentProgress) {
    const form = document.getElementById('progressForm');
    form.action = `/task-assignments/${id}/progress/update`;

    const progressRange = document.getElementById('progress_range');
    const progressValue = document.getElementById('progress_value');
    const progressInput = document.getElementById('progress_percentage');

    progressRange.value = currentProgress;
    progressValue.textContent = currentProgress + '%';
    progressInput.value = currentProgress;

    progressRange.addEventListener('input', function() {
        progressValue.textContent = this.value + '%';
        progressInput.value = this.value;
    });

    new bootstrap.Modal(document.getElementById('progressModal')).show();
}

function approveTask(id) {
    const form = document.getElementById('approveForm');
    form.action = `/task-assignments/${id}/approve`;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}
</script>
@endpush
