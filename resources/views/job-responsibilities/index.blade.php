@extends('layouts.app')

@section('title', 'Tugas & Tanggung Jawab')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks text-primary"></i> Tugas & Tanggung Jawab
        </h1>
        <div class="btn-group">
            <a href="{{ route('job-responsibilities.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Tugas
            </a>
            <a href="{{ route('job-responsibilities.statistics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Statistik
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('job-responsibilities.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari nama tugas, deskripsi, atau jabatan..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jabatan</label>
                    <select name="position_id" class="form-select">
                        <option value="">Semua Jabatan</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" 
                                {{ request('position_id') == $position->id ? 'selected' : '' }}>
                                {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prioritas</label>
                    <select name="priority" class="form-select">
                        <option value="">Semua</option>
                        <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Kritis</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jenis Tugas</label>
                    <select name="is_core" class="form-select">
                        <option value="">Semua</option>
                        <option value="1" {{ request('is_core') == '1' ? 'selected' : '' }}>Tugas Inti</option>
                        <option value="0" {{ request('is_core') == '0' ? 'selected' : '' }}>Tugas Tambahan</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="{{ route('job-responsibilities.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Responsibilities Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Tugas</th>
                            <th>Jabatan</th>
                            <th>Prioritas</th>
                            <th>Frekuensi</th>
                            <th>Estimasi</th>
                            <th>Penugasan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($responsibilities as $responsibility)
                        <tr>
                            <td>{{ $loop->iteration + ($responsibilities->currentPage() - 1) * $responsibilities->perPage() }}</td>
                            <td>
                                <div class="fw-bold">{{ $responsibility->task_name }}</div>
                                @if($responsibility->task_description)
                                    <small class="text-muted">{{ Str::limit($responsibility->task_description, 50) }}</small>
                                @endif
                                @if($responsibility->is_core_responsibility)
                                    <br><small class="badge bg-success">Tugas Inti</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('positions.show', $responsibility->position_id) }}" 
                                   class="text-decoration-none">
                                    <span class="badge bg-info">{{ $responsibility->position->name }}</span>
                                </a>
                            </td>
                            <td>
                                @if($responsibility->priority == 'critical')
                                    <span class="badge bg-danger">Kritis</span>
                                @elseif($responsibility->priority == 'high')
                                    <span class="badge bg-warning">Tinggi</span>
                                @elseif($responsibility->priority == 'medium')
                                    <span class="badge bg-info">Sedang</span>
                                @else
                                    <span class="badge bg-secondary">Rendah</span>
                                @endif
                            </td>
                            <td>
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
                            </td>
                            <td>
                                @if($responsibility->estimated_hours)
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-clock"></i> {{ $responsibility->estimated_hours }} jam
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $responsibility->task_assignments_count > 0 ? 'primary' : 'secondary' }}">
                                    <i class="fas fa-clipboard-check"></i> {{ $responsibility->task_assignments_count }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('job-responsibilities.show', $responsibility->id) }}" 
                                       class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('job-responsibilities.edit', $responsibility->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            title="Hapus"
                                            onclick="confirmDelete({{ $responsibility->id }}, '{{ $responsibility->task_name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada data tugas</p>
                                <a href="{{ route('job-responsibilities.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Tugas Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($responsibilities->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan {{ $responsibilities->firstItem() }} - {{ $responsibilities->lastItem() }} dari {{ $responsibilities->total() }} data
                </div>
                <div>
                    {{ $responsibilities->links() }}
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