@extends('layouts.app')

@section('title', 'Jadwal Mendatang')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-plus text-primary"></i> Jadwal 7 Hari Mendatang
        </h1>
        <div class="btn-group">
            <a href="{{ route('duty-schedules.index') }}" class="btn btn-secondary">
                <i class="fas fa-calendar-alt"></i> Semua Jadwal
            </a>
            <a href="{{ route('duty-schedules.today') }}" class="btn btn-info">
                <i class="fas fa-calendar-day"></i> Hari Ini
            </a>
            <a href="{{ route('duty-schedules.weekly') }}" class="btn btn-warning">
                <i class="fas fa-calendar-week"></i> Mingguan
            </a>
        </div>
    </div>

    <!-- Upcoming Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Jadwal Mendatang</div>
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
                                Hari Esok</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $tomorrow = \Carbon\Carbon::tomorrow()->format('Y-m-d');
                                    $tomorrowCount = $groupedSchedules[$tomorrow] ? $groupedSchedules[$tomorrow]->count() : 0;
                                @endphp
                                {{ $tomorrowCount }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-forward fa-2x text-gray-300"></i>
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
                                Pengurus Sibuk</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $busyCommittees = $schedules->pluck('committee_id')->countBy();
                                    $maxSchedules = $busyCommittees->max();
                                @endphp
                                {{ $maxSchedules }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
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
                                Hari Dengan Jadwal</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ count($groupedSchedules) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Navigation -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="date-navigation">
                    @php
                        $tomorrow = \Carbon\Carbon::tomorrow();
                        $nextWeek = \Carbon\Carbon::today()->addDays(7);
                    @endphp
                    <span class="fw-bold text-primary">
                        <i class="fas fa-calendar"></i>
                        {{ $tomorrow->translatedFormat('d F Y') }} -
                        {{ $nextWeek->translatedFormat('d F Y') }}
                    </span>
                </div>
                <div class="btn-group">
                    <a href="{{ route('duty-schedules.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Jadwal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Schedules -->
    @if(count($groupedSchedules) > 0)
        @foreach($groupedSchedules as $date => $dailySchedules)
            @php
                $carbonDate = \Carbon\Carbon::parse($date);
                $isTomorrow = $carbonDate->isTomorrow();
                $isWeekend = $carbonDate->isWeekend();
                $dayName = $carbonDate->translatedFormat('l');
            @endphp

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center
                    {{ $isTomorrow ? 'bg-info text-white' : ($isWeekend ? 'bg-light-warning' : 'bg-light-primary') }}">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-calendar-day"></i> {{ $dayName }}, {{ $carbonDate->translatedFormat('d F Y') }}
                        @if($isTomorrow)
                            <span class="badge bg-light text-info">BESOK</span>
                        @endif
                    </h6>
                    <span class="badge bg-{{ $isTomorrow ? 'light' : 'secondary' }}">
                        {{ $dailySchedules->count() }} jadwal
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
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
                                @foreach($dailySchedules as $schedule)
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $schedule->committee->full_name }}</div>
                                        <small class="text-muted">
                                            {{ $schedule->committee->phone_number ?? 'No Telp' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($schedule->committee->position)
                                            <span class="badge bg-info">{{ $schedule->committee->position->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->location }}</td>
                                    <td>
                                        <span class="badge bg-{{ getDutyTypeColor($schedule->duty_type) }}">
                                            {{ ucfirst($schedule->duty_type) }}
                                        </span>
                                        @if($schedule->is_recurring)
                                            <br><small class="text-muted"><i class="fas fa-redo"></i></small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ getStatusColor($schedule->status) }}">
                                            {{ $schedule->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('duty-schedules.show', $schedule->id) }}"
                                               class="btn btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('duty-schedules.edit', $schedule->id) }}"
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        @if($schedules->hasPages())
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Menampilkan {{ $schedules->firstItem() }} - {{ $schedules->lastItem() }} dari {{ $schedules->total() }} jadwal
                    </div>
                    <div>
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else
        <div class="card shadow">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="fas fa-calendar-plus fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">Tidak ada jadwal mendatang</h4>
                    <p class="text-muted mb-4">Tidak ada jadwal untuk 7 hari ke depan.</p>
                    <a href="{{ route('duty-schedules.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Jadwal Pertama
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Busy Committees -->
    @php
        $busyCommittees = $schedules->pluck('committee_id')->countBy();
        $topBusyCommittees = $busyCommittees->sortDesc()->take(5);
    @endphp

    @if($topBusyCommittees->count() > 0)
    <div class="card shadow mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-user-clock"></i> Pengurus Tersibuk (7 Hari Mendatang)
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($topBusyCommittees as $committeeId => $count)
                    @php
                        $committee = $committees->firstWhere('id', $committeeId);
                        if (!$committee) continue;
                    @endphp
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="card border-left-warning h-100">
                            <div class="card-body text-center">
                                <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center mb-2"
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <h6 class="card-title mb-1">{{ $committee->full_name }}</h6>
                                @if($committee->position)
                                    <small class="text-muted">{{ $committee->position->name }}</small><br>
                                @endif
                                <span class="badge bg-warning mt-2">{{ $count }} jadwal</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .bg-light-primary {
        background-color: #e3f2fd !important;
    }

    .bg-light-warning {
        background-color: #fdf6e3 !important;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush

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

function getStatusColor($status) {
    $colors = [
        'pending' => 'info',
        'ongoing' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    return $colors[$status] ?? 'secondary';
}
@endphp
