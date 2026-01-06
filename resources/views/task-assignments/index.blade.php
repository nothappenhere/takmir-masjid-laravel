@extends('layouts.app')

@section('title', 'Penugasan Tugas')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-check text-primary"></i> Penugasan Tugas
        </h1>
        <div class="btn-group">
            <a href="{{ route('task-assignments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Buat Penugasan
            </a>
            <a href="{{ route('task-assignments.overdue') }}" class="btn btn-danger">
                <i class="fas fa-exclamation-triangle"></i> Terlambat
            </a>
            <a href="{{ route('task-assignments.statistics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Statistik
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('task-assignments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pengurus</label>
                    <select name="committee_id" class="form-select">
                        <option value="">Semua Pengurus</option>
                        @foreach($committees as $committee)
                            <option value="{{ $committee->id }}" 
                                {{ request('committee_id') == $committee->id ? 'selected' : '' }}>
                                {{ $committee->full_name }}
                                @if($committee->position)
                                    - {{ $committee->position->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tugas</label>
                    <select name="job_responsibility_id" class="form-select">
                        <option value="">Semua Tugas</option>
                        @foreach($jobResponsibilities as $responsibility)
                            <option value="{{ $responsibility->id }}" 
                                {{ request('job_responsibility_id') == $responsibility->id ? 'selected' : '' }}>
                                {{ $responsibility->task_name }}
                                @if($responsibility->position)
                                    ({{ $responsibility->position->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prioritas</label>
                    <select name="priority" class="form-select">
                        <option value="">Semua Prioritas</option>
                        <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Kritis</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Filter Khusus</label>
                    <select name="filter_type" class="form-select" onchange="toggleFilterOptions(this.value)">
                        <option value="">Pilih Filter</option>
                        <option value="overdue" {{ request('overdue') ? 'selected' : '' }}>Terlambat</option>
                        <option value="due_soon" {{ request('due_soon') ? 'selected' : '' }}>Deadline Dekat</option>
                        <option value="date_range" {{ request('date_from') || request('date_to') ? 'selected' : '' }}>Rentang Tanggal</option>
                        <option value="due_date_range" {{ request('due_date_from') || request('due_date_to') ? 'selected' : '' }}>Rentang Deadline</option>
                    </select>
                </div>
                <div class="col-md-3" id="dateRange" style="display: {{ request('date_from') || request('date_to') ? 'block' : 'none' }};">
                    <label class="form-label">Tanggal Penugasan</label>
                    <div class="input-group">
                        <input type="date" name="date_from" class="form-control" 
                               value="{{ request('date_from') }}" placeholder="Dari">
                        <span class="input-group-text">-</span>
                        <input type="date" name="date_to" class="form-control" 
                               value="{{ request('date_to') }}" placeholder="Sampai">
                    </div>
                </div>
                <div class="col-md-3" id="dueDateRange" style="display: {{ request('due_date_from') || request('due_date_to') ? 'block' : 'none' }};">
                    <label class="form-label">Tanggal Deadline</label>
                    <div class="input-group">
                        <input type="date" name="due_date_from" class="form-control" 
                               value="{{ request('due_date_from') }}" placeholder="Dari">
                        <span class="input-group-text">-</span>
                        <input type="date" name="due_date_to" class="form-control" 
                               value="{{ request('due_date_to') }}" placeholder="Sampai">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari catatan, nama pengurus, atau tugas..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('task-assignments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tugas</th>
                            <th>Pengurus</th>
                            <th>Tanggal</th>
                            <th>Deadline</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                        @php
                            $isOverdue = $assignment->is_overdue;
                        @endphp
                        <tr class="{{ $isOverdue ? 'table-danger' : ($assignment->status == 'completed' ? 'table-success' : '') }}">
                            <td>{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
                            <td>
                                <div class="fw-bold">{{ $assignment->jobResponsibility->task_name }}</div>
                                <small class="text-muted">
                                    @if($assignment->jobResponsibility->position)
                                        <i class="fas fa-sitemap"></i> {{ $assignment->jobResponsibility->position->name }}
                                    @else
                                        <i class="fas fa-sitemap"></i> Tidak ada posisi
                                    @endif
                                </small>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $assignment->committee->full_name }}</div>
                                @if($assignment->committee->position)
                                    <small class="text-muted">{{ $assignment->committee->position->name }}</small>
                                @endif
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($assignment->assigned_date)->format('d/m/Y') }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') }}
                                @if($isOverdue)
                                    <br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> Terlambat</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress grow" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $assignment->progress_percentage == 100 ? 'success' : 'info' }}" 
                                             style="width: {{ $assignment->progress_percentage }}%"></div>
                                    </div>
                                    <small class="ms-2">{{ $assignment->progress_percentage }}%</small>
                                </div>
                            </td>
                            <td>
                                @if($assignment->status == 'completed')
                                    <span class="badge bg-success">Selesai</span>
                                    @if($assignment->approved_by)
                                        <br><small class="text-success"><i class="fas fa-check"></i> Disetujui</small>
                                    @endif
                                @elseif($assignment->status == 'overdue' || $isOverdue)
                                    <span class="badge bg-danger">Terlambat</span>
                                @elseif($assignment->status == 'in_progress')
                                    <span class="badge bg-warning">Dalam Proses</span>
                                @elseif($assignment->status == 'pending')
                                    <span class="badge bg-info">Pending</span>
                                @else
                                    <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('task-assignments.show', $assignment->id) }}" 
                                       class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('task-assignments.edit', $assignment->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            title="Hapus"
                                            onclick="confirmDelete({{ $assignment->id }}, '{{ $assignment->jobResponsibility->task_name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada data penugasan</p>
                                <a href="{{ route('task-assignments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Buat Penugasan Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($assignments->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan {{ $assignments->firstItem() }} - {{ $assignments->lastItem() }} dari {{ $assignments->total() }} data
                </div>
                <div>
                    {{ $assignments->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus penugasan <strong id="deleteName"></strong>?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = `/task-assignments/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleFilterOptions(value) {
    document.getElementById('dateRange').style.display = 'none';
    document.getElementById('dueDateRange').style.display = 'none';
    
    if (value === 'date_range') {
        document.getElementById('dateRange').style.display = 'block';
    } else if (value === 'due_date_range') {
        document.getElementById('dueDateRange').style.display = 'block';
    } else if (value === 'overdue') {
        // Add hidden input for overdue
        if (!document.querySelector('input[name="overdue"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'overdue';
            input.value = 'true';
            document.querySelector('form').appendChild(input);
        }
    } else if (value === 'due_soon') {
        // Add hidden input for due_soon
        if (!document.querySelector('input[name="due_soon"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'due_soon';
            input.value = 'true';
            document.querySelector('form').appendChild(input);
        }
    }
}
</script>
@endpush