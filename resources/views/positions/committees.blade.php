@extends('layouts.app')

@section('title', 'Pengurus Berdasarkan Jabatan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-users text-primary"></i> Pengurus dengan Jabatan: {{ $position->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('positions.index') }}">Jabatan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('positions.show', $position->id) }}">{{ $position->name }}</a></li>
                    <li class="breadcrumb-item active">Daftar Pengurus</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('positions.show', $position->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Jabatan
            </a>
            <a href="{{ route('committees.create') }}?position_id={{ $position->id }}"
               class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Pengurus
            </a>
        </div>
    </div>

    <!-- Position Info Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-1">{{ $position->name }}</h4>
                    @if($position->description)
                        <p class="text-muted mb-2">{{ $position->description }}</p>
                    @endif
                    <div class="d-flex gap-2">
                        @if($position->level == 'leadership')
                            <span class="badge bg-danger">Kepemimpinan</span>
                        @elseif($position->level == 'division_head')
                            <span class="badge bg-warning">Kepala Divisi</span>
                        @elseif($position->level == 'staff')
                            <span class="badge bg-primary">Staf</span>
                        @else
                            <span class="badge bg-secondary">Relawan</span>
                        @endif

                        @if($position->status == 'active')
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Tidak Aktif</span>
                        @endif

                        @if($position->parent)
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-level-up-alt"></i> Atasan: {{ $position->parent->name }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="h2 text-primary mb-0">{{ $committees->total() }}</div>
                    <small class="text-muted">Total Pengurus</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $position->committees()->where('active_status', 'active')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                Laki-laki</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $position->committees()->where('gender', 'male')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-male fa-2x text-gray-300"></i>
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
                                Perempuan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $position->committees()->where('gender', 'female')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-female fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Tidak Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $position->committees()->where('active_status', 'inactive')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Committees Table -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Daftar Pengurus di Jabatan Ini
            </h6>
            <div class="btn-group">
                <a href="{{ route('committees.index') }}?position_id={{ $position->id }}"
                   class="btn btn-sm btn-info">
                    <i class="fas fa-external-link-alt"></i> Lihat di Semua Pengurus
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($committees->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>Kontak</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Bergabung</th>
                                <th>Tugas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($committees as $committee)
                            <tr>
                                <td>{{ $loop->iteration + ($committees->currentPage() - 1) * $committees->perPage() }}</td>
                                <td>
                                    <div class="fw-bold">{{ $committee->full_name }}</div>
                                    @if($committee->currentPositionHistory)
                                        <small class="text-muted">
                                            <i class="fas fa-history"></i>
                                            {{ \Carbon\Carbon::parse($committee->currentPositionHistory->start_date)->format('d/m/Y') }}
                                        </small>
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
                                    @if($committee->gender == 'male')
                                        <span class="badge bg-info">Laki-laki</span>
                                    @else
                                        <span class="badge bg-warning">Perempuan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($committee->active_status == 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif($committee->active_status == 'inactive')
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Mengundurkan Diri</span>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($committee->join_date)->format('d/m/Y') }}
                                    <div class="text-muted small">
                                        {{ \Carbon\Carbon::parse($committee->join_date)->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $totalTasks = $committee->taskAssignments()->count();
                                        $completedTasks = $committee->taskAssignments()->where('status', 'completed')->count();
                                    @endphp
                                    <div>
                                        <small class="text-muted">{{ $completedTasks }}/{{ $totalTasks }} selesai</small>
                                    </div>
                                    @if($totalTasks > 0)
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $completedTasks == $totalTasks ? 'success' : 'info' }}"
                                             style="width: {{ $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0 }}%"></div>
                                    </div>
                                    @endif
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
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($committees->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Menampilkan {{ $committees->firstItem() }} - {{ $committees->lastItem() }} dari {{ $committees->total() }} pengurus
                    </div>
                    <div>
                        {{ $committees->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Belum ada pengurus di jabatan ini</h4>
                    <p class="text-muted mb-4">Jabatan ini belum memiliki pengurus yang ditugaskan.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('committees.create') }}?position_id={{ $position->id }}"
                           class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Pengurus Pertama
                        </a>
                        <a href="{{ route('committees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-search"></i> Lihat Semua Pengurus
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Additional Information -->
    @if($committees->count() > 0)
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Distribusi Gender
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $maleCount = $position->committees()->where('gender', 'male')->count();
                        $femaleCount = $position->committees()->where('gender', 'female')->count();
                        $totalCount = $maleCount + $femaleCount;
                        $malePercentage = $totalCount > 0 ? ($maleCount / $totalCount) * 100 : 0;
                        $femalePercentage = $totalCount > 0 ? ($femaleCount / $totalCount) * 100 : 0;
                    @endphp
                    <div class="row">
                        <div class="col-md-6 text-center">
                            <div class="h1 text-primary mb-0">{{ $maleCount }}</div>
                            <span class="badge bg-info">Laki-laki</span>
                            <div class="mt-2">
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: {{ $malePercentage }}%">
                                        {{ number_format($malePercentage, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="h1 text-warning mb-0">{{ $femaleCount }}</div>
                            <span class="badge bg-warning">Perempuan</span>
                            <div class="mt-2">
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: {{ $femalePercentage }}%">
                                        {{ number_format($femalePercentage, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Distribusi Status
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $activeCount = $position->committees()->where('active_status', 'active')->count();
                        $inactiveCount = $position->committees()->where('active_status', 'inactive')->count();
                        $resignedCount = $position->committees()->where('active_status', 'resigned')->count();
                        $totalStatusCount = $activeCount + $inactiveCount + $resignedCount;
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Jumlah</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>{{ $activeCount }}</td>
                                    <td>
                                        @php
                                            $activePercentage = $totalStatusCount > 0 ? ($activeCount / $totalStatusCount) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $activePercentage }}%">
                                                {{ number_format($activePercentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">Tidak Aktif</span></td>
                                    <td>{{ $inactiveCount }}</td>
                                    <td>
                                        @php
                                            $inactivePercentage = $totalStatusCount > 0 ? ($inactiveCount / $totalStatusCount) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-secondary" style="width: {{ $inactivePercentage }}%">
                                                {{ number_format($inactivePercentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">Mengundurkan Diri</span></td>
                                    <td>{{ $resignedCount }}</td>
                                    <td>
                                        @php
                                            $resignedPercentage = $totalStatusCount > 0 ? ($resignedCount / $totalStatusCount) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-danger" style="width: {{ $resignedPercentage }}%">
                                                {{ number_format($resignedPercentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
