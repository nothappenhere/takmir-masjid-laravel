@extends('layouts.app')

@section('title', 'Data Pengurus')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users text-primary"></i> Data Pengurus
        </h1>
        <a href="{{ route('committees.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Pengurus
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('committees.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nama, email, atau telepon..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="resigned" {{ request('status') == 'resigned' ? 'selected' : '' }}>Mengundurkan Diri</option>
                    </select>
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
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select">
                        <option value="">Semua</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('committees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Committees Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Lengkap</th>
                            <th>Jabatan</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>Tanggal Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($committees as $committee)
                        <tr>
                            <td>{{ $loop->iteration + ($committees->currentPage() - 1) * $committees->perPage() }}</td>
                            <td>
                                <div class="fw-bold">{{ $committee->full_name }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-{{ $committee->gender == 'male' ? 'male' : 'female' }}"></i>
                                    {{ $committee->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                </small>
                            </td>
                            <td>
                                @if($committee->position)
                                    <span class="badge bg-info">{{ $committee->position->name }}</span>
                                @else
                                    <span class="text-muted">Tidak ada jabatan</span>
                                @endif
                            </td>
                            <td>
                                @if($committee->email)
                                    <div><i class="fas fa-envelope"></i> {{ $committee->email }}</div>
                                @endif
                                @if($committee->phone_number)
                                    <div><i class="fas fa-phone"></i> {{ $committee->phone_number }}</div>
                                @endif
                            </td>
                            <td>
                                @if($committee->active_status == 'active')
                                    <span class="badge bg-success">Aktif</span>
                                @elseif($committee->active_status == 'inactive')
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @else
                                    <span class="badge bg-warning">Mengundurkan Diri</span>
                                @endif
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($committee->join_date)->format('d/m/Y') }}
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($committee->join_date)->diffForHumans() }}
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('committees.show', $committee->id) }}" 
                                       class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('committees.edit', $committee->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            title="Hapus"
                                            onclick="confirmDelete({{ $committee->id }}, '{{ $committee->full_name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada data pengurus</p>
                                <a href="{{ route('committees.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Pengurus Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($committees->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan {{ $committees->firstItem() }} - {{ $committees->lastItem() }} dari {{ $committees->total() }} data
                </div>
                <div>
                    {{ $committees->links() }}
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
                <p>Apakah Anda yakin ingin menghapus pengurus <strong id="deleteName"></strong>?</p>
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
    document.getElementById('deleteForm').action = `/committees/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush