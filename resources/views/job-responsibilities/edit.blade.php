@extends('layouts.app')

@section('title', 'Edit Tugas')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit text-warning"></i> Edit Tugas
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('job-responsibilities.update', $responsibility->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6 mb-3">
                                <label for="position_id" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <select class="form-select @error('position_id') is-invalid @enderror" 
                                        id="position_id" name="position_id" required>
                                    <option value="">Pilih Jabatan</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}" 
                                            {{ old('position_id', $responsibility->position_id) == $position->id ? 'selected' : '' }}>
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
                                       value="{{ old('task_name', $responsibility->task_name) }}" required>
                                @error('task_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="task_description" class="form-label">Deskripsi Tugas</label>
                                <textarea class="form-control @error('task_description') is-invalid @enderror" 
                                          id="task_description" name="task_description" rows="3">{{ old('task_description', $responsibility->task_description) }}</textarea>
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
                                    <option value="critical" {{ old('priority', $responsibility->priority) == 'critical' ? 'selected' : '' }}>Kritis</option>
                                    <option value="high" {{ old('priority', $responsibility->priority) == 'high' ? 'selected' : '' }}>Tinggi</option>
                                    <option value="medium" {{ old('priority', $responsibility->priority) == 'medium' ? 'selected' : '' }}>Sedang</option>
                                    <option value="low" {{ old('priority', $responsibility->priority) == 'low' ? 'selected' : '' }}>Rendah</option>
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
                                    <option value="daily" {{ old('frequency', $responsibility->frequency) == 'daily' ? 'selected' : '' }}>Harian</option>
                                    <option value="weekly" {{ old('frequency', $responsibility->frequency) == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                    <option value="monthly" {{ old('frequency', $responsibility->frequency) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="yearly" {{ old('frequency', $responsibility->frequency) == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                                    <option value="as_needed" {{ old('frequency', $responsibility->frequency) == 'as_needed' ? 'selected' : '' }}>Sesuai Kebutuhan</option>
                                </select>
                                @error('frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="estimated_hours" class="form-label">Estimasi Waktu (jam)</label>
                                <input type="number" class="form-control @error('estimated_hours') is-invalid @enderror" 
                                       id="estimated_hours" name="estimated_hours" 
                                       value="{{ old('estimated_hours', $responsibility->estimated_hours) }}" 
                                       min="0" max="100" step="0.5">
                                @error('estimated_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_core_responsibility" name="is_core_responsibility" value="1"
                                           {{ old('is_core_responsibility', $responsibility->is_core_responsibility) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_core_responsibility">
                                        Tugas Inti (Core Responsibility)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('job-responsibilities.show', $responsibility->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection