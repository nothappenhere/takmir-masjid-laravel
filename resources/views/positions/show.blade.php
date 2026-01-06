@extends('layouts.app')

@section('title', 'Detail Jabatan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-sitemap text-primary"></i> Detail Jabatan
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('positions.index') }}">Jabatan</a></li>
                    <li class="breadcrumb-item active">{{ $position->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('positions.edit', $position->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('positions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Position Information -->
        <div class="col-lg-4 mb-4">
            <!-- Position Card -->
            <div class="card shadow">
                <div class="card-body">
                    <!-- Position Header -->
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-user-tie fa-2x text-white"></i>
                        </div>
                        <h4 class="card-title mt-3">{{ $position->name }}</h4>
                        
                        <!-- Level Badge -->
                        <div class="mb-3">
                            @if($position->level == 'leadership')
                                <span class="badge bg-danger p-2">Kepemimpinan</span>
                            @elseif($position->level == 'division_head')
                                <span class="badge bg-warning p-2">Kepala Divisi</span>
                            @elseif($position->level == 'staff')
                                <span class="badge bg-primary p-2">Staf</span>
                            @else
                                <span class="badge bg-secondary p-2">Relawan</span>
                            @endif
                            
                            <!-- Status Badge -->
                            @if($position->status == 'active')
                                <span class="badge bg-success p-2">Aktif</span>
                            @else
                                <span class="badge bg-secondary p-2">Tidak Aktif</span>
                            @endif
                        </div>
                    </div>

                    <!-- Hierarchy Information -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3"><i class="fas fa-network-wired"></i> Struktur Hierarki</h6>
                        <ul class="list-unstyled">
                            @if($position->parent)
                            <li class="mb-2">
                                <i class="fas fa-level-up-alt text-primary me-2"></i>
                                <strong>Atasan:</strong> 
                                <a href="{{ route('positions.show', $position->parent->id) }}">
                                    {{ $position->parent->name }}
                                </a>
                            </li>
                            @endif
                            @if($position->children_count > 0)
                            <li class="mb-2">
                                <i class="fas fa-level-down-alt text-primary me-2"></i>
                                <strong>Bawahan:</strong> {{ $position->children_count }} jabatan
                            </li>
                            @endif
                            <li>
                                <i class="fas fa-sort-numeric-up text-primary me-2"></i>
                                <strong>Urutan:</strong> {{ $position->order }}
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Statistics -->
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h4 mb-0 text-primary">{{ $committeeStats['total'] }}</div>
                            <small class="text-muted">Total Pengurus</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-primary">{{ $responsibilityStats['total'] }}</div>
                            <small class="text-muted">Total Tugas</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Card -->
            @if($position->description)
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Deskripsi Jabatan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $position->description }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Committees & Responsibilities -->
        <div class="col-lg-8">
            <!-- Committees Card -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users"></i> Pengurus di Jabatan Ini
                        <span class="badge bg-primary ms-2">{{ $committeeStats['total'] }}</span>
                    </h6>
                    <a href="{{ route('positions.committees', $position->id) }}" class="btn btn-sm btn-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    <!-- Committee Statistics -->
                    <div class="row mb-4">
                        <div class="col-3 text-center">
                            <div class="h5 mb-0 text-success">{{ $committeeStats['active'] }}</div>
                            <small class="text-muted">Aktif</small>
                        </div>
                        <div class="col-3 text-center">
                            <div class="h5 mb-0 text-secondary">{{ $committeeStats['inactive'] }}</div>
                            <small class="text-muted">Tidak Aktif</small>
                        </div>
                        <div class="col-3 text-center">
                            <div class="h5 mb-0 text-warning">{{ $committeeStats['resigned'] }}</div>
                            <small class="text-muted">Mengundurkan Diri</small>
                        </div>
                        <div class="col-3 text-center">
                            <div class="h5 mb-0 text-info">{{ $committeeStats['total'] }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>

                    <!-- Committees List -->
                    @if($position->committees->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Status</th>
                                        <th>Bergabung</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($position->committees as $committee)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $committee->full_name }}</div>
                                            <small class="text-muted">
                                                <i class="fas fa-{{ $committee->gender == 'male' ? 'male' : 'female' }}"></i>
                                                {{ $committee->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                            </small>
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
                                        </td>
                                        <td>
                                            <a href="{{ route('committees.show', $committee->id) }}" 
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
                            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada pengurus di jabatan ini</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Responsibilities Card -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Tugas & Tanggung Jawab
                        <span class="badge bg-primary ms-2">{{ $responsibilityStats['total'] }}</span>
                    </h6>
                    <a href="{{ route('positions.responsibilities', $position->id) }}" class="btn btn-sm btn-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    <!-- Responsibility Statistics -->
                    <div class="row mb-4">
                        <div class="col-4 text-center">
                            <div class="h5 mb-0 text-primary">{{ $responsibilityStats['core'] }}</div>
                            <small class="text-muted">Tugas Inti</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="h5 mb-0 text-secondary">{{ $responsibilityStats['non_core'] }}</div>
                            <small class="text-muted">Tugas Tambahan</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="h5 mb-0 text-warning">{{ $responsibilityStats['by_priority']['high'] ?? 0 }}</div>
                            <small class="text-muted">Prioritas Tinggi</small>
                        </div>
                    </div>

                    <!-- Responsibilities List -->
                    @if($position->responsibilities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tugas</th>
                                        <th>Prioritas</th>
                                        <th>Frekuensi</th>
                                        <th>Jenis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($position->responsibilities as $responsibility)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $responsibility->task_name }}</div>
                                            @if($responsibility->task_description)
                                                <small class="text-muted">{{ Str::limit($responsibility->task_description, 50) }}</small>
                                            @endif
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
                                            @else
                                                <span class="badge bg-secondary">{{ $responsibility->frequency }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($responsibility->is_core_responsibility)
                                                <span class="badge bg-success">Inti</span>
                                            @else
                                                <span class="badge bg-secondary">Tambahan</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada tugas untuk jabatan ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection