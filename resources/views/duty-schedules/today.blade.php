@extends('layouts.app')

@section('title', 'Jadwal Hari Ini')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-day text-primary"></i> Jadwal Hari Ini
            <small class="text-muted">({{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }})</small>
        </h1>
        <div class="btn-group">
            <a href="{{ route('duty-schedules.index') }}" class="btn btn-secondary">
                <i class="fas fa-calendar-alt"></i> Semua Jadwal
            </a>
            <a href="{{ route('duty-schedules.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
        </div>
    </div>

    <!-- Today's Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Jadwal</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schedules->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                Selesai</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schedules->where('status', 'completed')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Sedang Berjalan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schedules->where('status', 'ongoing')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
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
                                Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schedules->where('status', 'pending')->count() }}
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

    <!-- Today's Schedules -->
    <div class="card shadow">
        <div class="card-body">
            @if($schedules->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengurus</th>
                                <th>Jabatan</th>
                                <th>Lokasi</th>
                                <th>Jenis Tugas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                            <tr class="{{ $schedule->status == 'ongoing' ? 'table-warning' : '' }}">
                                <td>
                                    <div class="fw-bold">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    </div>
                                    @php
                                        $now = now();
                                        $start = \Carbon\Carbon::parse($schedule->start_time);
                                        $end = \Carbon\Carbon::parse($schedule->end_time);
                                        $currentTime = \Carbon\Carbon::parse($now->format('H:i'));
                                    @endphp
                                    @if($currentTime->between($start, $end) && $schedule->status == 'ongoing')
                                        <small class="text-success"><i class="fas fa-play-circle"></i> Sedang berlangsung</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $schedule->committee->full_name }}</div>
                                    <small class="text-muted">
                                        <i class="fas fa-{{ $schedule->committee->gender == 'male' ? 'male' : 'female' }}"></i>
                                        {{ $schedule->committee->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                    </small>
                                </td>
                                <td>
                                    @if($schedule->committee->position)
                                        <span class="badge bg-info">{{ $schedule->committee->position->name }}</span>
                                    @else
                                        <span class="text-muted">Tidak ada jabatan</span>
                                    @endif
                                </td>
                                <td>{{ $schedule->location }}</td>
                                <td>
                                    <span class="badge bg-{{ getDutyTypeColor($schedule->duty_type) }}">
                                        {{ ucfirst($schedule->duty_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($schedule->status == 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif($schedule->status == 'ongoing')
                                        <span class="badge bg-warning">Sedang Berjalan</span>
                                    @elseif($schedule->status == 'pending')
                                        <span class="badge bg-info">Pending</span>
                                    @else
                                        <span class="badge bg-secondary">Dibatalkan</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('duty-schedules.show', $schedule->id) }}"
                                           class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('duty-schedules.edit', $schedule->id) }}"
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
                @if($schedules->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Menampilkan {{ $schedules->firstItem() }} - {{ $schedules->lastItem() }} dari {{ $schedules->total() }} data
                    </div>
                    <div>
                        {{ $schedules->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-check fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">Tidak ada jadwal untuk hari ini</h4>
                    <p class="text-muted mb-4">Anda belum membuat jadwal untuk hari ini.</p>
                    <a href="{{ route('duty-schedules.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Jadwal Hari Ini
                    </a>
                </div>
            @endif
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
