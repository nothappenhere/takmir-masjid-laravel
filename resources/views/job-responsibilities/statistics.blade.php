@extends('layouts.app')

@section('title', 'Statistik Tugas')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary"></i> Statistik Tugas & Tanggung Jawab
        </h1>
        <a href="{{ route('job-responsibilities.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Overview Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tugas</div>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tugas Inti</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['core_vs_non_core'][1] ?? 0 }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Jabatan dengan Tugas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['positions_with_responsibilities'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sitemap fa-2x text-gray-300"></i>
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
                                Rata-rata Estimasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['average_hours'], 1) }} jam
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

    <div class="row">
        <!-- Priority Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-triangle"></i> Distribusi Berdasarkan Prioritas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Prioritas</th>
                                    <th>Jumlah Tugas</th>
                                    <th>Persentase</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['by_priority'] as $item)
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
                                    <td class="text-center">{{ $item->count }}</td>
                                    <td>
                                        <div class="progress">
                                            @php
                                                $percentage = $stats['total'] > 0 ? ($item->count / $stats['total']) * 100 : 0;
                                                $color = match($item->priority) {
                                                    'critical' => 'danger',
                                                    'high' => 'warning',
                                                    'medium' => 'info',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <div class="progress-bar bg-{{ $color }}" 
                                                 style="width: {{ $percentage }}%">
                                                {{ number_format($percentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($item->priority == 'critical' && $item->count > 5)
                                            <span class="badge bg-danger">Perlu Perhatian</span>
                                        @elseif($item->priority == 'high' && $item->count > 10)
                                            <span class="badge bg-warning">Tinggi</span>
                                        @else
                                            <span class="badge bg-success">Normal</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Frequency Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-redo"></i> Distribusi Berdasarkan Frekuensi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Frekuensi</th>
                                    <th>Jumlah Tugas</th>
                                    <th>Persentase</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['by_frequency'] as $item)
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
                                    <td class="text-center">{{ $item->count }}</td>
                                    <td>
                                        <div class="progress">
                                            @php
                                                $percentage = $stats['total'] > 0 ? ($item->count / $stats['total']) * 100 : 0;
                                            @endphp
                                            <div class="progress-bar bg-primary" 
                                                 style="width: {{ $percentage }}%">
                                                {{ number_format($percentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->frequency == 'daily' && $item->count > 20)
                                            <small class="text-danger">Beban harian tinggi</small>
                                        @elseif($item->frequency == 'weekly' && $item->count > 15)
                                            <small class="text-warning">Beban mingguan sedang</small>
                                        @else
                                            <small class="text-success">Beban normal</small>
                                        @endif
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

    <!-- Top Positions -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy"></i> 5 Jabatan dengan Tugas Terbanyak
                    </h6>
                </div>
                <div class="card-body">
                    @if($topPositions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Peringkat</th>
                                        <th>Nama Jabatan</th>
                                        <th>Jumlah Tugas</th>
                                        <th>Distribusi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topPositions as $index => $position)
                                    <tr>
                                        <td class="text-center">
                                            @if($index == 0)
                                                <span class="badge bg-warning">1</span>
                                            @elseif($index == 1)
                                                <span class="badge bg-secondary">2</span>
                                            @elseif($index == 2)
                                                <span class="badge bg-danger">3</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('positions.show', $position->id) }}" 
                                               class="text-decoration-none">
                                                {{ $position->name }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $position->responsibility_count }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $maxCount = $topPositions->max('responsibility_count');
                                                $percentage = $maxCount > 0 ? ($position->responsibility_count / $maxCount) * 100 : 0;
                                            @endphp
                                            <div class="progress">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $percentage }}%">
                                                    {{ $position->responsibility_count }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('job-responsibilities.by-position', $position->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Lihat Tugas
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada data tugas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Ringkasan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="h2 text-primary">{{ $stats['core_vs_non_core'][1] ?? 0 }}</div>
                            <p class="text-muted">Tugas Inti</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="h2 text-secondary">{{ $stats['core_vs_non_core'][0] ?? 0 }}</div>
                            <p class="text-muted">Tugas Tambahan</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="h2 text-success">{{ $stats['total'] }}</div>
                            <p class="text-muted">Total Tugas</p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <p class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Sistem memiliki total <strong>{{ $stats['total'] }}</strong> tugas yang terdistribusi di 
                            <strong>{{ $stats['positions_with_responsibilities'] }}</strong> jabatan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection