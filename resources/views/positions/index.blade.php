@extends('layouts.app')

@section('title', 'Data Jabatan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-sitemap text-primary"></i> Data Jabatan
        </h1>
        <div class="btn-group">
            <a href="{{ route('positions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Jabatan
            </a>
            <a href="{{ route('positions.index', ['view' => 'tree']) }}" class="btn btn-info">
                <i class="fas fa-project-diagram"></i> Tampilan Pohon
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('positions.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nama jabatan atau deskripsi..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Level</label>
                    <select name="level" class="form-select">
                        <option value="">Semua Level</option>
                        <option value="leadership" {{ request('level') == 'leadership' ? 'selected' : '' }}>Kepemimpinan</option>
                        <option value="division_head" {{ request('level') == 'division_head' ? 'selected' : '' }}>Kepala Divisi</option>
                        <option value="staff" {{ request('level') == 'staff' ? 'selected' : '' }}>Staf</option>
                        <option value="volunteer" {{ request('level') == 'volunteer' ? 'selected' : '' }}>Relawan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('positions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Positions Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Jabatan</th>
                            <th>Parent</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th>Statistik</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($positions as $position)
                        <tr>
                            <td>{{ $loop->iteration + ($positions->currentPage() - 1) * $positions->perPage() }}</td>
                            <td>
                                <div class="fw-bold">{{ $position->name }}</div>
                                @if($position->description)
                                    <small class="text-muted">{{ Str::limit($position->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($position->parent)
                                    <span class="badge bg-info">{{ $position->parent->name }}</span>
                                @else
                                    <span class="text-muted">Root</span>
                                @endif
                            </td>
                            <td>
                                @if($position->level == 'leadership')
                                    <span class="badge bg-danger">Kepemimpinan</span>
                                @elseif($position->level == 'division_head')
                                    <span class="badge bg-warning">Kepala Divisi</span>
                                @elseif($position->level == 'staff')
                                    <span class="badge bg-primary">Staf</span>
                                @else
                                    <span class="badge bg-secondary">Relawan</span>
                                @endif
                            </td>
                            <td>
                                @if($position->status == 'active')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-info" title="Pengurus">
                                        <i class="fas fa-users"></i> {{ $position->committees_count }}
                                    </span>
                                    <span class="badge bg-warning" title="Tugas">
                                        <i class="fas fa-tasks"></i> {{ $position->responsibilities_count }}
                                    </span>
                                    <span class="badge bg-secondary" title="Sub-jabatan">
                                        <i class="fas fa-sitemap"></i> {{ $position->children_count }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('positions.show', $position->id) }}" 
                                       class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('positions.edit', $position->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            title="Hapus"
                                            onclick="confirmDelete({{ $position->id }}, '{{ $position->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada data jabatan</p>
                                <a href="{{ route('positions.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Jabatan Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($positions->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan {{ $positions->firstItem() }} - {{ $positions->lastItem() }} dari {{ $positions->total() }} data
                </div>
                <div>
                    {{ $positions->links() }}
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
                <p>Apakah Anda yakin ingin menghapus jabatan <strong id="deleteName"></strong>?</p>
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
    document.getElementById('deleteForm').action = `/positions/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush