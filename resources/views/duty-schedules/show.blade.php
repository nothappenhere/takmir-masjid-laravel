@extends('layouts.app')

@section('title', 'Detail Jadwal')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-alt text-primary"></i> Detail Jadwal
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('duty-schedules.index') }}">Jadwal</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('duty-schedules.edit', $schedule->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('duty-schedules.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Schedule Details -->
        <div class="col-lg-6 mb-4">
            <!-- Schedule Information Card -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informasi Jadwal
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Tanggal:</strong><br>
                            <span class="text-primary">
                                {{ \Carbon\Carbon::parse($schedule->duty_date)->translatedFormat('l, d F Y') }}
                            </span>
                        </div>
                        <div class="col-6">
                            <strong>Waktu:</strong><br>
                            <span class="text-primary">
                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Jenis Tugas:</strong><br>
                            <span class="badge bg-{{ getDutyTypeColor($schedule->duty_type) }}">
                                {{ ucfirst($schedule->duty_type) }}
                            </span>
                        </div>
                        <div class="col-6">
                            <strong>Status:</strong><br>
                            @if($schedule->status == 'completed')
                                <span class="badge bg-success">Selesai</span>
                            @elseif($schedule->status == 'ongoing')
                                <span class="badge bg-warning">Sedang Berjalan</span>
                            @elseif($schedule->status == 'pending')
                                <span class="badge bg-info">Pending</span>
                            @else
                                <span class="badge bg-secondary">Dibatalkan</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Lokasi:</strong><br>
                        <span class="text-primary">{{ $schedule->location }}</span>
                    </div>

                    @if($schedule->is_recurring)
                    <div class="mb-3">
                        <strong>Jadwal Berulang:</strong><br>
                        <span class="text-info">
                            <i class="fas fa-redo"></i> {{ ucfirst($schedule->recurring_type) }}
                            @if($schedule->recurring_end_date)
                                sampai {{ \Carbon\Carbon::parse($schedule->recurring_end_date)->format('d/m/Y') }}
                            @endif
                        </span>
                    </div>
                    @endif

                    @if($schedule->remarks)
                    <div class="mb-3">
                        <strong>Catatan:</strong><br>
                        <p class="mb-0">{{ $schedule->remarks }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Committee Information Card -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user"></i> Informasi Pengurus
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user fa-2x text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1">{{ $schedule->committee->full_name }}</h5>
                            @if($schedule->committee->position)
                                <span class="badge bg-info">{{ $schedule->committee->position->name }}</span>
                            @endif
                        </div>
                    </div>

                    <ul class="list-unstyled">
                        @if($schedule->committee->email)
                        <li class="mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            {{ $schedule->committee->email }}
                        </li>
                        @endif
                        @if($schedule->committee->phone_number)
                        <li class="mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            {{ $schedule->committee->phone_number }}
                        </li>
                        @endif
                        <li class="mb-2">
                            <i class="fas fa-{{ $schedule->committee->gender == 'male' ? 'male' : 'female' }} text-primary me-2"></i>
                            {{ $schedule->committee->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                        </li>
                        <li>
                            <i class="fas fa-calendar-plus text-primary me-2"></i>
                            Bergabung: {{ \Carbon\Carbon::parse($schedule->committee->join_date)->format('d/m/Y') }}
                        </li>
                    </ul>

                    <div class="text-center mt-3">
                        <a href="{{ route('committees.show', $schedule->committee->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-external-link-alt"></i> Lihat Profil Lengkap
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Actions & Similar Schedules -->
        <div class="col-lg-6">
            <!-- Status Update Card -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-sync-alt"></i> Update Status
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('duty-schedules.update-status', $schedule->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="status" class="form-label">Status Baru</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" {{ $schedule->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="ongoing" {{ $schedule->status == 'ongoing' ? 'selected' : '' }}>Sedang Berjalan</option>
                                    <option value="completed" {{ $schedule->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="cancelled" {{ $schedule->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Catatan Status (Opsional)</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2"
                                      placeholder="Catatan tentang perubahan status"></textarea>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Similar Schedules Card -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-check"></i> Jadwal Serupa
                        <small class="text-muted">(Pengurus & Jenis Tugas yang Sama)</small>
                    </h6>
                </div>
                <div class="card-body">
                    @if($similarSchedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($similarSchedules as $similar)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($similar->duty_date)->format('d/m/Y') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($similar->start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($similar->end_time)->format('H:i') }}
                                        </td>
                                        <td>
                                            @if($similar->status == 'completed')
                                                <span class="badge bg-success">Selesai</span>
                                            @elseif($similar->status == 'ongoing')
                                                <span class="badge bg-warning">Sedang Berjalan</span>
                                            @elseif($similar->status == 'pending')
                                                <span class="badge bg-info">Pending</span>
                                            @else
                                                <span class="badge bg-secondary">Dibatalkan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('duty-schedules.show', $similar->id) }}"
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
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada jadwal serupa lainnya</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@php
function getDutyTypeColor($type) {
    $colors = [
        'piket' => 'primary',
        'kebersihan' => 'success',
        'keamanan' => 'dark',
        'administrasi' => 'info',
        'lainnya' => 'secondary',
    ];
    return $colors[$type] ?? 'secondary';
}
@endphp
