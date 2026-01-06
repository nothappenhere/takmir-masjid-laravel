@extends('layouts.app')

@section('title', 'Detail Tugas')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tasks text-primary"></i> Detail Tugas
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('job-responsibilities.index') }}">Tugas</a></li>
                    <li class="breadcrumb-item active">{{ $responsibility->task_name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('job-responsibilities.edit', $responsibility->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('job-responsibilities.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Task Information -->
        <div class="col-lg-6 mb-4">
            <!-- Task Information Card -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informasi Tugas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h4>{{ $responsibility->task_name }}</h4>
                        @if($responsibility->is_core_responsibility)
                            <span class="badge bg-success">Tugas Inti</span>
                        @else
                            <span class="badge bg-secondary">Tugas Tambahan</span>
                        @endif
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Jabatan:</strong><br>
                            <a href="{{ route('positions.show', $responsibility->position_id) }}" 
                               class="text-decoration-none">
                                <span class="badge bg-info">{{ $responsibility->position->name }}</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <strong>Prioritas:</strong><br>
                            @if($responsibility->priority == 'critical')
                                <span class="badge bg-danger">Kritis</span>
                            @elseif($responsibility->priority == 'high')
                                <span class="badge bg-warning">Tinggi</span>
                            @elseif($responsibility->priority == 'medium')
                                <span class="badge bg-info">Sedang</span>
                            @else
                                <span class="badge bg-secondary">Rendah</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Frekuensi:</strong><br>
                            @if($responsibility->frequency == 'daily')
                                <span class="badge bg-dark">Harian</span>
                            @elseif($responsibility->frequency == 'weekly')
                                <span class="badge bg-primary">Mingguan</span>
                            @elseif($responsibility->frequency == 'monthly')
                                <span class="badge bg-info">Bulanan</span>
                            @elseif($responsibility->frequency == 'yearly')
                                <span class="badge bg-warning">Tahunan</span>
                            @else
                                <span class="badge bg-secondary">Sesuai Kebutuhan</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Estimasi Waktu:</strong><br>
                            @if($responsibility->estimated_hours)
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-clock"></i> {{ $responsibility->estimated_hours }} jam
                                </span>
                            @else
                                <span class="text-muted">Tidak ditentukan</span>
                            @endif
                        </div>
                    </div>

                    @if($responsibility->task_description)
                    <div class="mb-3">
                        <strong>Deskripsi:</strong><br>
                        <p class="mb-0">{{ $responsibility->task_description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Statistik Penugasan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="h5 mb-0 text-primary">{{ $stats['total_assignments'] }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-3">
                            <div class="h5 mb-0 text-warning">{{ $stats['active_assignments'] }}</div>
                            <small class="text-muted">Aktif</small>
                        </div>
                        <div class="col-3">
                            <div class="h5 mb-0 text-success">{{ $stats['completed_assignments'] }}</div>
                            <small class="text-muted">Selesai</small>
                        </div>
                        <div class="col-3">
                            <div class="h5 mb-0 text-danger">{{ $stats['overdue_assignments'] }}</div>
                            <small class="text-muted">Terlambat</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Assignments & Actions -->
        <div class="col-lg-6">
            <!-- Recent Assignments Card -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-check"></i> Penugasan Terbaru
                        <span class="badge bg-primary ms-2">{{ $stats['total_assignments'] }}</span>
                    </h6>
                    <a href="{{ route('task-assignments.create') }}?job_responsibility_id={{ $responsibility->id }}" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Buat Penugasan
                    </a>
                </div>
                <div class="card-body">
                    @if($recentAssignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Pengurus</th>
                                        <th>Deadline</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAssignments as $assignment)
                                    <tr class="{{ $assignment->status == 'overdue' ? 'table-danger' : '' }}">
                                        <td>
                                            <div class="fw-bold">{{ $assignment->committee->full_name }}</div>
                                            @if($assignment->committee->position)
                                                <small class="text-muted">{{ $assignment->committee->position->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') }}
                                            @if($assignment->due_date < now() && $assignment->status != 'completed')
                                                <br><small class="text-danger">Terlambat</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $assignment->progress_percentage == 100 ? 'success' : 'info' }}" 
                                                     style="width: {{ $assignment->progress_percentage }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $assignment->progress_percentage }}%</small>
                                        </td>
                                        <td>
                                            @if($assignment->status == 'completed')
                                                <span class="badge bg-success">Selesai</span>
                                            @elseif($assignment->status == 'overdue')
                                                <span class="badge bg-danger">Terlambat</span>
                                            @elseif($assignment->status == 'in_progress')
                                                <span class="badge bg-warning">Dalam Proses</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('task-assignments.show', $assignment->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada penugasan untuk tugas ini</p>
                            <a href="{{ route('task-assignments.create') }}?job_responsibility_id={{ $responsibility->id }}" 
                               class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Penugasan Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('positions.show', $responsibility->position_id) }}" 
                               class="btn btn-outline-primary w-100">
                                <i class="fas fa-sitemap"></i> Lihat Jabatan
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('job-responsibilities.by-position', $responsibility->position_id) }}" 
                               class="btn btn-outline-info w-100">
                                <i class="fas fa-list"></i> Tugas Lainnya
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('task-assignments.index') }}?job_responsibility_id={{ $responsibility->id }}" 
                               class="btn btn-outline-warning w-100">
                                <i class="fas fa-clipboard-check"></i> Semua Penugasan
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="button" class="btn btn-outline-danger w-100" 
                                    onclick="confirmDelete({{ $responsibility->id }}, '{{ $responsibility->task_name }}')">
                                <i class="fas fa-trash"></i> Hapus Tugas
                            </button>
                        </div>
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
                <p>Apakah Anda yakin ingin menghapus tugas <strong id="deleteName"></strong>?</p>
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
    document.getElementById('deleteForm').action = `/job-responsibilities/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush