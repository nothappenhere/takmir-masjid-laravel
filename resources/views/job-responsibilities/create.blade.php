@extends('layouts.app')

@section('title', 'Tambah Tugas Baru')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks text-primary"></i> Tambah Tugas Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('job-responsibilities.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6 mb-3">
                                <label for="position_id" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <select class="form-select @error('position_id') is-invalid @enderror" 
                                        id="position_id" name="position_id" required>
                                    <option value="">Pilih Jabatan</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}" 
                                            {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                            {{ $position->name }}
                                            @if($position->level)
                                                ({{ $position->level }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="task_name" class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('task_name') is-invalid @enderror" 
                                       id="task_name" name="task_name" 
                                       value="{{ old('task_name') }}" 
                                       placeholder="Contoh: Memimpin rapat, Membuat laporan keuangan" required>
                                @error('task_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="task_description" class="form-label">Deskripsi Tugas</label>
                                <textarea class="form-control @error('task_description') is-invalid @enderror" 
                                          id="task_description" name="task_description" rows="3"
                                          placeholder="Deskripsi detail tentang tugas ini, tujuan, dan hasil yang diharapkan">{{ old('task_description') }}</textarea>
                                @error('task_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority & Frequency -->
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">Prioritas <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority" required>
                                    <option value="">Pilih Prioritas</option>
                                    <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Kritis</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="frequency" class="form-label">Frekuensi <span class="text-danger">*</span></label>
                                <select class="form-select @error('frequency') is-invalid @enderror" 
                                        id="frequency" name="frequency" required>
                                    <option value="">Pilih Frekuensi</option>
                                    <option value="daily" {{ old('frequency') == 'daily' ? 'selected' : '' }}>Harian</option>
                                    <option value="weekly" {{ old('frequency') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                    <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="yearly" {{ old('frequency') == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                                    <option value="as_needed" {{ old('frequency') == 'as_needed' ? 'selected' : '' }}>Sesuai Kebutuhan</option>
                                </select>
                                @error('frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="estimated_hours" class="form-label">Estimasi Waktu (jam)</label>
                                <input type="number" class="form-control @error('estimated_hours') is-invalid @enderror" 
                                       id="estimated_hours" name="estimated_hours" 
                                       value="{{ old('estimated_hours') }}" 
                                       min="0" max="100" step="0.5"
                                       placeholder="Contoh: 2, 4, 8">
                                <div class="form-text">Estimasi waktu pengerjaan dalam jam</div>
                                @error('estimated_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_core_responsibility" name="is_core_responsibility" value="1"
                                           {{ old('is_core_responsibility', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_core_responsibility">
                                        Tugas Inti (Core Responsibility)
                                    </label>
                                </div>
                                <div class="form-text">
                                    Centang jika ini adalah tugas inti dari jabatan tersebut
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('job-responsibilities.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection