@extends('layouts.app')

@section('title', 'Tugas Berdasarkan Jabatan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tasks text-primary"></i> Tugas untuk Jabatan: {{ $position->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('job-responsibilities.index') }}">Tugas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('positions.show', $position->id) }}">{{ $position->name }}</a></li>
                    <li class="breadcrumb-item active">Daftar Tugas</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('positions.show', $position->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Jabatan
            </a>
            <a href="{{ route('job-responsibilities.create') }}?position_id={{ $position->id }}"
               class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Tugas
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
                        <span class="badge bg-info">{{ $position->level ?? 'Tidak ada level' }}</span>
                        <span class="badge bg-{{ $position->status == 'active' ? 'success' : 'secondary' }}">
                            {{ $position->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                        @if($position->parent)
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-level-up-alt"></i> Atasan: {{ $position->parent->name }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="h2 text-primary mb-0">{{ $responsibilities->total() }}</div>
                    <small class="text-muted">Total Tugas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tugas Inti</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $position->responsibilities()->where('is_core_responsibility', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
                                Tugas Kritis</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $position->responsibilities()->where('priority', 'critical')->count() }}
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
                                Penugasan Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $activeAssignments = \App\Models\TaskAssignment::whereHas('jobResponsibility', function($q) use ($position) {
                                        $q->where('position_id', $position->id);
                                    })->whereIn('status', ['pending', 'in_progress'])->count();
                                @endphp
                                {{ $activeAssignments }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
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
                                Rata-rata Estimasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $avgHours = $position->responsibilities()->avg('estimated_hours');
                                @endphp
                                {{ number_format($avgHours, 1) }} jam
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsibilities Table -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Daftar Tugas untuk Jabatan Ini
            </h6>
            <div class="btn-group">
                <a href="{{ route('positions.responsibilities', $position->id) }}"
                   class="btn btn-sm btn-info">
                    <i class="fas fa-sitemap"></i> Tugas di Hierarki
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($responsibilities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Tugas</th>
                                <th>Prioritas</th>
                                <th>Frekuensi</th>
                                <th>Estimasi</th>
                                <th>Penugasan</th>
                                <th>Jenis</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($responsibilities as $responsibility)
                            <tr>
                                <td>{{ $loop->iteration + ($responsibilities->currentPage() - 1) * $responsibilities->perPage() }}</td>
                                <td>
                                    <div class="fw-bold">{{ $responsibility->task_name }}</div>
                                    @if($responsibility->task_description)
                                        <small class="text-muted">{{ Str::limit($responsibility->task_description, 60) }}</small>
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
                                    @if($responsibility->is_core_responsibility)
                                        <span class="badge bg-success">Inti</span>
                                    @else
                                        <span class="badge bg-secondary">Tambahan</span>
                                    @endif
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
                                        <a href="{{ route('task-assignments.create') }}?job_responsibility_id={{ $responsibility->id }}"
                                           class="btn btn-sm btn-primary" title="Buat Penugasan">
                                            <i class="fas fa-clipboard-check"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($responsibilities->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Menampilkan {{ $responsibilities->firstItem() }} - {{ $responsibilities->lastItem() }} dari {{ $responsibilities->total() }} tugas
                    </div>
                    <div>
                        {{ $responsibilities->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Belum ada tugas untuk jabatan ini</h4>
                    <p class="text-muted mb-4">Jabatan ini belum memiliki tugas atau tanggung jawab yang didefinisikan.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('job-responsibilities.create') }}?position_id={{ $position->id }}"
                           class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Tugas Pertama
                        </a>
                        <a href="{{ route('job-responsibilities.index') }}" class="btn btn-secondary">
                            <i class="fas fa-search"></i> Lihat Semua Tugas
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Summary -->
    @if($responsibilities->count() > 0)
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Distribusi Prioritas
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $priorities = $position->responsibilities()
                            ->selectRaw('priority, count(*) as count')
                            ->groupBy('priority')
                            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
                            ->get();
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Prioritas</th>
                                    <th>Jumlah</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($priorities as $item)
                                <tr>
                                    <td>
                                        @if($item->priority == 'critical')
                                            <span class="badge bg-danger">Kritis</span>
                                        @elseif($item->priority == 'high')
                                            <span class="badge bg-warning">Tinggi</span>
                                        @elseif($item->priority == 'medium')
                                            <span class="badge bg-info">Sedang</span>
                                        @else
                                            <span class="badge bg-secondary">Rendah</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->count }}</td>
                                    <td>
                                        @php
                                            $percentage = $responsibilities->total() > 0 ? ($item->count / $responsibilities->total()) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ getPriorityColor($item->priority) }}"
                                                 style="width: {{ $percentage }}%">
                                                {{ number_format($percentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Distribusi Frekuensi
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $frequencies = $position->responsibilities()
                            ->selectRaw('frequency, count(*) as count')
                            ->groupBy('frequency')
                            ->orderBy('frequency')
                            ->get();
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Frekuensi</th>
                                    <th>Jumlah</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($frequencies as $item)
                                <tr>
                                    <td>
                                        @if($item->frequency == 'daily')
                                            <span class="badge bg-dark">Harian</span>
                                        @elseif($item->frequency == 'weekly')
                                            <span class="badge bg-primary">Mingguan</span>
                                        @elseif($item->frequency == 'monthly')
                                            <span class="badge bg-info">Bulanan</span>
                                        @elseif($item->frequency == 'yearly')
                                            <span class="badge bg-warning">Tahunan</span>
                                        @else
                                            <span class="badge bg-secondary">Sesuai Kebutuhan</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->count }}</td>
                                    <td>
                                        @php
                                            $percentage = $responsibilities->total() > 0 ? ($item->count / $responsibilities->total()) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-primary"
                                                 style="width: {{ $percentage }}%">
                                                {{ number_format($percentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Assignments -->
    @php
        $recentAssignments = \App\Models\TaskAssignment::whereHas('jobResponsibility', function($q) use ($position) {
            $q->where('position_id', $position->id);
        })->with(['committee', 'jobResponsibility'])
          ->latest()
          ->limit(5)
          ->get();
    @endphp

    @if($recentAssignments->count() > 0)
    <div class="card shadow mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clipboard-list"></i> Penugasan Terbaru untuk Jabatan Ini
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tugas</th>
                            <th>Pengurus</th>
                            <th>Deadline</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAssignments as $assignment)
                        <tr>
                            <td>{{ $assignment->jobResponsibility->task_name }}</td>
                            <td>{{ $assignment->committee->full_name }}</td>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
@endsection

@php
function getPriorityColor($priority) {
    $colors = [
        'critical' => 'danger',
        'high' => 'warning',
        'medium' => 'info',
        'low' => 'secondary',
    ];
    return $colors[$priority] ?? 'secondary';
}
@endphp
