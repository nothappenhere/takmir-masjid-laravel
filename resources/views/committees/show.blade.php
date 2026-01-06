@extends('layouts.app')

@section('title', 'Detail Pengurus')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user text-primary"></i> Detail Pengurus
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('committees.index') }}">Pengurus</a></li>
                    <li class="breadcrumb-item active">{{ $committee->full_name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('committees.edit', $committee->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('committees.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Profile Information -->
        <div class="col-lg-4 mb-4">
            <!-- Profile Card -->
            <div class="card shadow">
                <div class="card-body text-center">
                    <!-- Profile Image -->
                    <div class="mb-3">
                        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    </div>
                    
                    <!-- Name & Position -->
                    <h4 class="card-title">{{ $committee->full_name }}</h4>
                    @if($committee->position)
                        <div class="badge bg-info fs-6 mb-3">{{ $committee->position->name }}</div>
                    @endif
                    
                    <!-- Status Badge -->
                    <div class="mb-3">
                        @if($committee->active_status == 'active')
                            <span class="badge bg-success p-2 fs-6">Aktif</span>
                        @elseif($committee->active_status == 'inactive')
                            <span class="badge bg-secondary p-2 fs-6">Tidak Aktif</span>
                        @else
                            <span class="badge bg-warning p-2 fs-6">Mengundurkan Diri</span>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="row text-center mt-4">
                        <div class="col-6 border-end">
                            <div class="h4 mb-0">{{ $stats['total_duties'] }}</div>
                            <small class="text-muted">Total Tugas</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0">{{ $stats['completed_tasks'] }}</div>
                            <small class="text-muted">Tugas Selesai</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-address-card"></i> Informasi Kontak
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @if($committee->email)
                        <li class="mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <a href="mailto:{{ $committee->email }}">{{ $committee->email }}</a>
                        </li>
                        @endif
                        @if($committee->phone_number)
                        <li class="mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <a href="tel:{{ $committee->phone_number }}">{{ $committee->phone_number }}</a>
                        </li>
                        @endif
                        @if($committee->address)
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            {{ $committee->address }}
                        </li>
                        @endif
                        <li class="mb-2">
                            <i class="fas fa-{{ $committee->gender == 'male' ? 'male' : 'female' }} text-primary me-2"></i>
                            {{ $committee->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-birthday-cake text-primary me-2"></i>
                            {{ $committee->birth_date ? \Carbon\Carbon::parse($committee->birth_date)->format('d/m/Y') : 'Tidak diketahui' }}
                        </li>
                        <li>
                            <i class="fas fa-calendar-plus text-primary me-2"></i>
                            Bergabung: {{ \Carbon\Carbon::parse($committee->join_date)->format('d/m/Y') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column: Details & Activities -->
        <div class="col-lg-8">
            <!-- Position History -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Riwayat Jabatan
                    </h6>
                    <span class="badge bg-primary">
                        {{ $committee->positionHistories->count() }} Riwayat
                    </span>
                </div>
                <div class="card-body">
                    @if($committee->positionHistories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Jabatan</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                        <th>Jenis Penunjukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($committee->positionHistories as $history)
                                    <tr class="{{ $history->is_active ? 'table-active' : '' }}">
                                        <td>{{ $history->position->name }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($history->start_date)->format('d/m/Y') }}
                                            -
                                            {{ \Carbon\Carbon::parse($history->end_date)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            @if($history->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $history->appointment_type }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Belum ada riwayat jabatan</p>
                    @endif
                </div>
            </div>

            <!-- Recent Duties -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> Jadwal Terbaru
                    </h6>
                    <a href="{{ route('committees.duties', $committee->id) }}" class="btn btn-sm btn-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    @if($committee->dutySchedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Lokasi</th>
                                        <th>Tugas</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($committee->dutySchedules as $duty)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($duty->duty_date)->format('d/m/Y') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($duty->start_time)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($duty->end_time)->format('H:i') }}
                                        </td>
                                        <td>{{ $duty->location }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $duty->duty_type }}</span>
                                        </td>
                                        <td>
                                            @if($duty->status == 'completed')
                                                <span class="badge bg-success">Selesai</span>
                                            @elseif($duty->status == 'ongoing')
                                                <span class="badge bg-warning">Sedang Berjalan</span>
                                            @else
                                                <span class="badge bg-info">{{ $duty->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Belum ada jadwal tugas</p>
                    @endif
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Tugas Terbaru
                    </h6>
                    <a href="{{ route('committees.tasks', $committee->id) }}" class="btn btn-sm btn-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    @if($committee->taskAssignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tugas</th>
                                        <th>Tanggal</th>
                                        <th>Deadline</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($committee->taskAssignments as $task)
                                    <tr>
                                        <td>{{ $task->jobResponsibility->task_name ?? 'Tugas' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($task->assigned_date)->format('d/m/Y') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                            @if($task->due_date < now() && $task->status != 'completed')
                                                <br><small class="text-danger">Terlambat</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 6px;">
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
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Belum ada penugasan</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection