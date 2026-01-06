@extends('layouts.app')

@section('title', 'Struktur Organisasi')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-project-diagram text-primary"></i> Struktur Organisasi
        </h1>
        <div class="btn-group">
            <a href="{{ route('positions.index') }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> Tampilan Daftar
            </a>
            <a href="{{ route('positions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Jabatan
            </a>
        </div>
    </div>

    <!-- Organization Chart -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-sitemap"></i> Diagram Struktur Jabatan
            </h6>
        </div>
        <div class="card-body">
            <div class="organization-chart">
                @foreach($positions as $position)
                    <div class="mb-4">
                        <!-- Root Level -->
                        <div class="root-position text-center mb-3">
                            <div class="position-card mx-auto">
                                <div class="position-header bg-primary text-white p-3 rounded-top">
                                    <h5 class="mb-0">{{ $position->name }}</h5>
                                    <small>
                                        <i class="fas fa-users"></i> {{ $position->committees_count }} Pengurus
                                    </small>
                                </div>
                                <div class="position-body bg-white p-3 rounded-bottom border">
                                    <div class="d-flex justify-content-center gap-2 mb-2">
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
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('positions.show', $position->id) }}" 
                                           class="btn btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('positions.edit', $position->id) }}" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Children Level 1 -->
                        @if($position->children->count() > 0)
                        <div class="children-level">
                            <div class="text-center mb-2">
                                <i class="fas fa-arrow-down text-muted"></i>
                            </div>
                            <div class="row justify-content-center">
                                @foreach($position->children as $child)
                                <div class="col-md-4 mb-3">
                                    <div class="position-card">
                                        <div class="position-header bg-info text-white p-2 rounded-top">
                                            <h6 class="mb-0">{{ $child->name }}</h6>
                                            <small>
                                                <i class="fas fa-users"></i> {{ $child->committees_count }} Pengurus
                                            </small>
                                        </div>
                                        <div class="position-body bg-light p-2 rounded-bottom border">
                                            <div class="d-flex justify-content-center gap-1 mb-1">
                                                @if($child->level == 'division_head')
                                                    <span class="badge bg-warning">Divisi</span>
                                                @elseif($child->level == 'staff')
                                                    <span class="badge bg-primary">Staf</span>
                                                @else
                                                    <span class="badge bg-secondary">Relawan</span>
                                                @endif
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('positions.show', $child->id) }}" 
                                                   class="btn btn-info btn-sm" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('positions.edit', $child->id) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Children Level 2 -->
                                    @if($child->children->count() > 0)
                                    <div class="grandchildren-level mt-2">
                                        <div class="text-center mb-1">
                                            <i class="fas fa-arrow-down fa-xs text-muted"></i>
                                        </div>
                                        <div class="row">
                                            @foreach($child->children as $grandchild)
                                            <div class="col-12 mb-2">
                                                <div class="position-card-small">
                                                    <div class="d-flex justify-content-between align-items-center bg-white p-2 border rounded">
                                                        <div>
                                                            <strong class="small">{{ $grandchild->name }}</strong>
                                                            <div class="text-muted">
                                                                <small>
                                                                    <i class="fas fa-users"></i> {{ $grandchild->committees_count }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="btn-group btn-group-xs">
                                                            <a href="{{ route('positions.show', $grandchild->id) }}" 
                                                               class="btn btn-info btn-xs">
                                                                <i class="fas fa-eye fa-xs"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @if(!$loop->last)
                        <hr class="my-4">
                    @endif
                @endforeach
            </div>

            <!-- Legend -->
            <div class="mt-4 pt-4 border-top">
                <h6 class="text-muted mb-3"><i class="fas fa-key"></i> Keterangan:</h6>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <span class="badge bg-danger">Kepemimpinan</span>
                        <small class="text-muted"> - Pimpinan tertinggi</small>
                    </div>
                    <div class="col-md-3 mb-2">
                        <span class="badge bg-warning">Kepala Divisi</span>
                        <small class="text-muted"> - Kepala divisi/departemen</small>
                    </div>
                    <div class="col-md-3 mb-2">
                        <span class="badge bg-primary">Staf</span>
                        <small class="text-muted"> - Anggota staf</small>
                    </div>
                    <div class="col-md-3 mb-2">
                        <span class="badge bg-secondary">Relawan</span>
                        <small class="text-muted"> - Relawan/sukarelawan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.organization-chart {
    position: relative;
}
.position-card {
    max-width: 300px;
    margin: 0 auto;
    transition: transform 0.2s;
}
.position-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.position-card-small {
    font-size: 0.9rem;
}
.children-level {
    position: relative;
}
.children-level::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    width: 2px;
    height: 20px;
    background-color: #dee2e6;
    transform: translateX(-50%);
}
.grandchildren-level {
    position: relative;
}
.grandchildren-level::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    width: 2px;
    height: 10px;
    background-color: #dee2e6;
    transform: translateX(-50%);
}
</style>
@endsection