@extends('layouts.app')

@section('title', 'Detail Penugasan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-clipboard-check text-primary"></i> Detail Penugasan
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('task-assignments.index') }}">Penugasan</a></li>
                    <li class="breadcrumb-item active">#{{ $assignment->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('task-assignments.edit', $assignment->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('task-assignments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Column: Assignment Details -->
        <div class="col-lg-8">
            <!-- Assignment Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informasi Penugasan
                    </h6>
                    <span class="badge bg-{{ $assignment->status == 'completed' ? 'success' : ($assignment->is_overdue ? 'danger' : 'warning') }}">
                        {{ strtoupper($assignment->status) }}
                        @if($assignment->is_overdue && $assignment->status != 'overdue')
                            (TERLAMBAT)
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Tugas</h6>
                            <h5 class="mb-3">{{ $assignment->jobResponsibility->task_name }}</h5>
                            
                            <h6 class="text-muted mt-4">Pengurus</h6>
                            <div class="d-flex align-items-center">
                                <div class="shrink-0">
                                    <i class="fas fa-user-circle fa-2x text-primary"></i>
                                </div>
                                <div class="grow ms-3">
                                    <h5 class="mb-1">{{ $assignment->committee->full_name }}</h5>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-sitemap"></i> 
                                        {{ $assignment->committee->position->name ?? 'Tidak ada posisi' }}
                                    </p>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-phone"></i> {{ $assignment->committee->phone_number ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Timeline</h6>
                            <div class="timeline">
                                <div class="timeline-item mb-3">
                                    <span class="timeline-icon"><i class="fas fa-calendar-plus"></i></span>
                                    <div class="timeline-content">
                                        <small class="text-muted">Tanggal Penugasan</small>
                                        <p class="mb-0 fw-bold">
                                            {{ \Carbon\Carbon::parse($assignment->assigned_date)->format('d F Y') }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item mb-3">
                                    <span class="timeline-icon {{ $assignment->is_overdue ? 'text-danger' : '' }}">
                                        <i class="fas fa-calendar-check"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <small class="text-muted">Deadline</small>
                                        <p class="mb-0 fw-bold {{ $assignment->is_overdue ? 'text-danger' : '' }}">
                                            {{ \Carbon\Carbon::parse($assignment->due_date)->format('d F Y') }}
                                            @if($assignment->is_overdue)
                                                <span class="badge bg-danger ms-2">Terlambat</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if($assignment->completed_date)
                                <div class="timeline-item mb-3">
                                    <span class="timeline-icon text-success"><i class="fas fa-check-circle"></i></span>
                                    <div class="timeline-content">
                                        <small class="text-muted">Tanggal Selesai</small>
                                        <p class="mb-0 fw-bold text-success">
                                            {{ \Carbon\Carbon::parse($assignment->completed_date)->format('d F Y') }}
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Progress</span>
                            <span class="fw-bold">{{ $assignment->progress_percentage }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar progress-bar-striped bg-{{ $assignment->progress_percentage == 100 ? 'success' : 'info' }}" 
                                 role="progressbar" style="width: {{ $assignment->progress_percentage }}%">
                                {{ $assignment->progress_percentage }}%
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($assignment->notes)
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Catatan</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $assignment->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Progress Update Form -->
            @if(!in_array($assignment->status, ['completed', 'cancelled']))
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Update Progress
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('task-assignments.update-progress', $assignment->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <label for="progress_percentage" class="form-label">Progress Saat Ini (%)</label>
                                <input type="range" class="form-range" 
                                       id="progress_percentage" name="progress_percentage" 
                                       min="0" max="100" step="5" 
                                       value="{{ $assignment->progress_percentage }}"
                                       oninput="updateProgressValue(this.value)">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">0%</small>
                                    <span id="progressValue" class="fw-bold">{{ $assignment->progress_percentage }}%</span>
                                    <small class="text-muted">100%</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="notes" class="form-label">Update Catatan</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Tambahkan catatan update...">{{ $assignment->notes }}</textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Progress
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Approval Section -->
            @if($assignment->status == 'completed' && !$assignment->approved_by)
            <div class="card shadow mb-4 border-warning">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-check-circle"></i> Persetujuan Tugas
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-warning">Tugas ini telah selesai dan menunggu persetujuan.</p>
                    <form action="{{ route('task-assignments.approve', $assignment->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label for="approver_id" class="form-label">Disetujui Oleh <span class="text-danger">*</span></label>
                                <select class="form-select" id="approver_id" name="approver_id" required>
                                    <option value="">Pilih Pengurus</option>
                                    @foreach($committees as $committee)
                                        @if($committee->id != $assignment->committee_id && $committee->active_status == 'active')
                                            <option value="{{ $committee->id }}">
                                                {{ $committee->full_name }}
                                                @if($committee->position)
                                                    - {{ $committee->position->name }}
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="approval_notes" class="form-label">Catatan Persetujuan</label>
                                <textarea class="form-control" id="approval_notes" name="notes" rows="3" 
                                          placeholder="Catatan persetujuan..."></textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Setujui Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif($assignment->approved_by && $assignment->approver)
            <div class="card shadow mb-4 border-success">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-check-circle"></i> Tugas Telah Disetujui
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-user-check fa-3x text-success"></i>
                        </div>
                        <div class="grow ms-3">
                            <h5 class="mb-1">Disetujui oleh {{ $assignment->approver->full_name }}</h5>
                            <p class="mb-0 text-muted">
                                Pada {{ \Carbon\Carbon::parse($assignment->approved_at)->format('d F Y H:i') }}
                            </p>
                            @if($assignment->notes)
                            <div class="mt-2">
                                <small class="text-muted">Catatan persetujuan:</small>
                                <p class="mb-0">{{ $assignment->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Related Information -->
        <div class="col-lg-4">
            <!-- Task Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Detail Tugas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Posisi yang Bertanggung Jawab:</small>
                        <p class="mb-2">
                            <i class="fas fa-sitemap text-primary"></i> 
                            {{ $assignment->jobResponsibility->position->name ?? 'Tidak ada posisi' }}
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Prioritas:</small>
                        <p class="mb-2">
                            @if($assignment->jobResponsibility->priority == 'critical')
                                <span class="badge bg-danger">Kritis</span>
                            @elseif($assignment->jobResponsibility->priority == 'high')
                                <span class="badge bg-warning">Tinggi</span>
                            @elseif($assignment->jobResponsibility->priority == 'medium')
                                <span class="badge bg-info">Sedang</span>
                            @elseif($assignment->jobResponsibility->priority == 'low')
                                <span class="badge bg-success">Rendah</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Deskripsi:</small>
                        <p class="mb-0">{{ $assignment->jobResponsibility->description ?? 'Tidak ada deskripsi' }}</p>
                    </div>
                </div>
            </div>

            <!-- Similar Assignments Card -->
            @if($similarAssignments->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-link"></i> Penugasan Terkait
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($similarAssignments as $similar)
                        <a href="{{ route('task-assignments.show', $similar->id) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $similar->jobResponsibility->task_name }}</h6>
                                <small class="text-{{ $similar->status == 'completed' ? 'success' : ($similar->is_overdue ? 'danger' : 'warning') }}">
                                    {{ $similar->status }}
                                </small>
                            </div>
                            <p class="mb-1 small">{{ $similar->committee->full_name }}</p>
                            <small class="text-muted">
                                Deadline: {{ \Carbon\Carbon::parse($similar->due_date)->format('d/m/Y') }}
                            </small>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!in_array($assignment->status, ['completed', 'cancelled']))
                            @if($assignment->progress_percentage < 100)
                            <button class="btn btn-success" onclick="markAsComplete()">
                                <i class="fas fa-check"></i> Tandai Selesai (100%)
                            </button>
                            @endif
                        @endif
                        
                        @if(in_array($assignment->status, ['pending', 'in_progress']))
                        <a href="{{ route('task-assignments.edit', $assignment->id) }}" 
                           class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Penugasan
                        </a>
                        @endif
                        
                        @if(!in_array($assignment->status, ['completed']) && !$assignment->approved_by)
                        <button class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Hapus Penugasan
                        </button>
                        @endif
                    </div>
                </div>
            </div>
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
                <p>Apakah Anda yakin ingin menghapus penugasan ini?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('task-assignments.destroy', $assignment->id) }}" method="POST">
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
function updateProgressValue(value) {
    document.getElementById('progressValue').textContent = value + '%';
}

function markAsComplete() {
    if (confirm('Apakah Anda yakin ingin menandai tugas ini sebagai selesai?')) {
        fetch('{{ route("task-assignments.update-progress", $assignment->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                progress_percentage: 100,
                _method: 'PATCH'
            })
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Gagal mengupdate progress');
            }
        });
    }
}

function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e3e6f0;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-icon {
    position: absolute;
    left: -30px;
    background: #fff;
    color: #4e73df;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e3e6f0;
}
</style>
@endpush