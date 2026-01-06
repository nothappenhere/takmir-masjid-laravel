@extends('layouts.app')

@section('title', 'Statistik Penugasan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary"></i> Statistik Penugasan
        </h1>
        <div class="btn-group">
            <a href="{{ route('task-assignments.index') }}" class="btn btn-primary">
                <i class="fas fa-clipboard-check"></i> Daftar Penugasan
            </a>
            <a href="{{ route('task-assignments.overdue') }}" class="btn btn-danger">
                <i class="fas fa-exclamation-triangle"></i> Terlambat
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Penugasan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Terselesaikan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['by_status']['completed'] ?? 0 }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success mr-2">
                                    <i class="fas fa-arrow-up"></i> {{ number_format($stats['completion_rate'], 1) }}%
                                </span>
                                <span>Tingkat Penyelesaian</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Dalam Proses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['in_progress_assignments'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Terlambat
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['overdue'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Detailed Statistics -->
    <div class="row">
        <!-- Status Distribution -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribusi Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Selesai
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Dalam Proses
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Pending
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Terlambat
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-secondary"></i> Dibatalkan
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Metrik Kinerja</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-info">
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="h1 font-weight-bold text-info">
                                            {{ number_format($stats['average_completion_time'], 1) }}
                                        </div>
                                        <div class="text-muted">Rata-rata Hari Penyelesaian</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-success">
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="h1 font-weight-bold text-success">
                                            {{ number_format($stats['approval_rate'], 1) }}%
                                        </div>
                                        <div class="text-muted">Tingkat Persetujuan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="h1 font-weight-bold text-primary">
                                            {{ $stats['completed_this_month'] }}
                                        </div>
                                        <div class="text-muted">Selesai Bulan Ini</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-left-warning">
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="h1 font-weight-bold text-warning">
                                            {{ $stats['pending_assignments'] }}
                                        </div>
                                        <div class="text-muted">Menunggu Pengerjaan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Committees & Responsibilities -->
    <div class="row">
        <!-- Top Committees -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Top 5 Pengurus Aktif</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Pengurus</th>
                                    <th>Jumlah Tugas</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCommittees as $index => $committee)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $committee->full_name }}</td>
                                    <td>{{ $committee->assignment_count }}</td>
                                    <td>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-primary" 
                                                 style="width: {{ ($committee->assignment_count / $stats['total']) * 100 }}%">
                                            </div>
                                        </div>
                                        <small>{{ number_format(($committee->assignment_count / $stats['total']) * 100, 1) }}%</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Responsibilities -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Top 5 Tugas Terbanyak</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Tugas</th>
                                    <th>Jumlah Penugasan</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topResponsibilities as $index => $responsibility)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $responsibility->task_name }}</td>
                                    <td>{{ $responsibility->assignment_count }}</td>
                                    <td>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: {{ ($responsibility->assignment_count / $stats['total']) * 100 }}%">
                                            </div>
                                        </div>
                                        <small>{{ number_format(($responsibility->assignment_count / $stats['total']) * 100, 1) }}%</small>
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

    <!-- Monthly Completion Trend -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trend Penyelesaian Bulanan</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.chart-pie {
    position: relative;
    height: 250px;
}
.chart-area {
    position: relative;
    height: 300px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Status Distribution Pie Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: ['Selesai', 'Dalam Proses', 'Pending', 'Terlambat', 'Dibatalkan'],
        datasets: [{
            data: [
                {{ $stats['by_status']['completed'] ?? 0 }},
                {{ $stats['by_status']['in_progress'] ?? 0 }},
                {{ $stats['by_status']['pending'] ?? 0 }},
                {{ $stats['by_status']['overdue'] ?? 0 }},
                {{ $stats['by_status']['cancelled'] ?? 0 }}
            ],
            backgroundColor: [
                '#1cc88a',
                '#f6c23e',
                '#36b9cc',
                '#e74a3b',
                '#858796'
            ],
            hoverBackgroundColor: [
                '#17a673',
                '#dda20a',
                '#2c9faf',
                '#be2617',
                '#6c757d'
            ],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        cutout: '50%',
    },
});

// Monthly Completion Trend Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

// Mock data for monthly completion (you should replace this with real data)
const monthlyData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
    datasets: [{
        label: 'Tugas Selesai',
        lineTension: 0.3,
        backgroundColor: "rgba(78, 115, 223, 0.05)",
        borderColor: "rgba(78, 115, 223, 1)",
        pointRadius: 3,
        pointBackgroundColor: "rgba(78, 115, 223, 1)",
        pointBorderColor: "rgba(78, 115, 223, 1)",
        pointHoverRadius: 3,
        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
        pointHitRadius: 10,
        pointBorderWidth: 2,
        data: [65, 59, 80, 81, 56, 55, 40, 45, 60, 70, 75, 80],
    }],
};

const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: monthlyData,
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                ticks: {
                    beginAtZero: true
                },
                grid: {
                    color: "rgb(234, 236, 244)",
                }
            }
        }
    }
});
</script>
@endpush