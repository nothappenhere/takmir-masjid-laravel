@extends('layouts.app')

@section('title', 'Jadwal Mingguan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-week text-primary"></i> Jadwal Minggu Ini
            <small class="text-muted">
                ({{ \Carbon\Carbon::parse($weekStart)->translatedFormat('d F Y') }} -
                {{ \Carbon\Carbon::parse($weekEnd)->translatedFormat('d F Y') }})
            </small>
        </h1>
        <div class="btn-group">
            <a href="{{ route('duty-schedules.index') }}" class="btn btn-secondary">
                <i class="fas fa-calendar-alt"></i> Semua Jadwal
            </a>
            <a href="{{ route('duty-schedules.today') }}" class="btn btn-info">
                <i class="fas fa-calendar-day"></i> Hari Ini
            </a>
            <a href="{{ route('duty-schedules.upcoming') }}" class="btn btn-warning">
                <i class="fas fa-calendar-plus"></i> Mendatang
            </a>
        </div>
    </div>

    <!-- Weekly Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Jadwal Minggu Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ collect($schedules)->flatten()->count() }}
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
                                Hari Dengan Jadwal</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ count($schedules) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
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
                                Jadwal Berlangsung</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ collect($schedules)->flatten()->where('status', 'ongoing')->count() }}
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
                                Pengurus Terlibat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ collect($schedules)->flatten()->pluck('committee_id')->unique()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule Cards -->
    <div class="row">
        @php
            $days = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ];

            $currentDate = \Carbon\Carbon::parse($weekStart);
            $weekDates = [];
            for ($i = 0; $i < 7; $i++) {
                $weekDates[$currentDate->format('Y-m-d')] = [
                    'date' => $currentDate->copy(),
                    'day_name' => $currentDate->format('l'),
                    'indonesian_day' => $days[$currentDate->format('l')] ?? $currentDate->format('l')
                ];
                $currentDate->addDay();
            }
        @endphp

        @foreach($weekDates as $dateKey => $dateInfo)
            @php
                $daySchedules = $schedules[$dateKey] ?? collect();
                $isToday = $dateInfo['date']->isToday();
                $isPast = $dateInfo['date']->isPast() && !$isToday;
                $isWeekend = $dateInfo['date']->isWeekend();
            @endphp

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow h-100 border-{{ $isToday ? 'primary' : ($isPast ? 'secondary' : 'light') }}">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center
                        {{ $isToday ? 'bg-primary text-white' : ($isWeekend ? 'bg-light-warning' : 'bg-light') }}">
                        <h6 class="m-0 font-weight-bold">
                            {{ $dateInfo['indonesian_day'] }}
                        </h6>
                        <span class="badge bg-{{ $isToday ? 'light' : 'secondary' }} text-{{ $isToday ? 'primary' : 'dark' }}">
                            {{ $dateInfo['date']->format('d/m') }}
                            @if($isToday)
                                <span class="badge bg-white text-primary">HARI INI</span>
                            @endif
                        </span>
                    </div>
                    <div class="card-body">
                        @if($daySchedules->count() > 0)
                            <div class="mb-3">
                                <small class="text-muted">
                                    {{ $daySchedules->count() }} jadwal
                                </small>
                            </div>

                            @foreach($daySchedules as $schedule)
                                <div class="border-left-{{ getDutyTypeColor($schedule->duty_type) }} border-left-3 pl-3 mb-3 pb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $schedule->committee->full_name }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ getDutyTypeColor($schedule->duty_type) }} mb-1">
                                                {{ ucfirst($schedule->duty_type) }}
                                            </span>
                                            <br>
                                            <small class="badge bg-{{ getStatusColor($schedule->status) }}">
                                                {{ $schedule->status }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small>
                                            <i class="fas fa-map-marker-alt"></i> {{ $schedule->location }}
                                        </small>
                                    </div>
                                    @if($schedule->committee->position)
                                        <div class="mt-1">
                                            <small class="badge bg-info">
                                                {{ $schedule->committee->position->name }}
                                            </small>
                                        </div>
                                    @endif
                                    <div class="mt-2">
                                        <a href="{{ route('duty-schedules.show', $schedule->id) }}"
                                           class="btn btn-sm btn-outline-primary btn-block">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Tidak ada jadwal</p>
                                @if(!$isPast)
                                    <a href="{{ route('duty-schedules.create') }}?duty_date={{ $dateKey }}"
                                       class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-plus"></i> Tambah Jadwal
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="card shadow mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="fas fa-bolt text-warning"></i> Tindakan Cepat
            </h5>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <a href="{{ route('duty-schedules.create') }}" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Jadwal Baru
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="{{ route('duty-schedules.today') }}" class="btn btn-info w-100">
                        <i class="fas fa-calendar-day"></i> Hari Ini
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="{{ route('duty-schedules.upcoming') }}" class="btn btn-warning w-100">
                        <i class="fas fa-calendar-plus"></i> 7 Hari Mendatang
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="button" class="btn btn-success w-100" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak Jadwal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-primary { border-left-color: #4e73df !important; }
    .border-left-success { border-left-color: #1cc88a !important; }
    .border-left-danger { border-left-color: #e74a3b !important; }
    .border-left-warning { border-left-color: #f6c23e !important; }
    .border-left-info { border-left-color: #36b9cc !important; }
    .border-left-dark { border-left-color: #5a5c69 !important; }
    .border-left-secondary { border-left-color: #858796 !important; }

    .card-header.bg-light-warning {
        background-color: #fdf6e3 !important;
    }

    @media print {
        .btn, .btn-group, .card-header.bg-primary {
            display: none !important;
        }

        .card {
            border: 1px solid #000 !important;
            page-break-inside: avoid;
        }

        .card-header {
            background-color: #fff !important;
            color: #000 !important;
            border-bottom: 1px solid #000 !important;
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
