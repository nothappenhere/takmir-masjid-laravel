@extends('layouts.app')

@section('title', 'Buat Penugasan Baru')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-check text-primary"></i> Buat Penugasan Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('task-assignments.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Assignment Information -->
                            <div class="col-md-6 mb-3">
                                <label for="committee_id" class="form-label">Pengurus <span class="text-danger">*</span></label>
                                <select class="form-select @error('committee_id') is-invalid @enderror" 
                                        id="committee_id" name="committee_id" required>
                                    <option value="">Pilih Pengurus</option>
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}" 
                                            {{ old('committee_id') == $committee->id ? 'selected' : '' }}>
                                            {{ $committee->full_name }}
                                            @if($committee->position)
                                                - {{ $committee->position->name }}
                                            @endif
                                            @if($committee->active_status != 'active')
                                                (Tidak Aktif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('committee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="job_responsibility_id" class="form-label">Tugas <span class="text-danger">*</span></label>
                                <select class="form-select @error('job_responsibility_id') is-invalid @enderror" 
                                        id="job_responsibility_id" name="job_responsibility_id" required>
                                    <option value="">Pilih Tugas</option>
                                    @foreach($jobResponsibilities as $responsibility)
                                        <option value="{{ $responsibility->id }}" 
                                            {{ old('job_responsibility_id') == $responsibility->id ? 'selected' : '' }}
                                            data-priority="{{ $responsibility->priority ?? 'medium' }}">
                                            {{ $responsibility->task_name }}
                                            @if($responsibility->position)
                                                ({{ $responsibility->position->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('job_responsibility_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="assigned_date" class="form-label">Tanggal Penugasan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('assigned_date') is-invalid @enderror" 
                                       id="assigned_date" name="assigned_date" 
                                       value="{{ old('assigned_date', $defaultDate) }}" required>
                                @error('assigned_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Tanggal Deadline</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                       id="due_date" name="due_date" 
                                       value="{{ old('due_date', $defaultDueDate) }}">
                                <small class="text-muted">Kosongkan untuk 7 hari dari tanggal penugasan</small>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="progress_percentage" class="form-label">Progress (%)</label>
                                <input type="range" class="form-range" 
                                       id="progress_percentage" name="progress_percentage" 
                                       min="0" max="100" step="5" 
                                       value="{{ old('progress_percentage', 0) }}"
                                       oninput="updateProgressValue(this.value)">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">0%</small>
                                    <span id="progressValue" class="fw-bold">0%</span>
                                    <small class="text-muted">100%</small>
                                </div>
                                @error('progress_percentage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Task Information Preview -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Tugas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Deskripsi:</small>
                                        <p id="taskDescription" class="mb-2">Pilih tugas untuk melihat deskripsi</p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Prioritas:</small>
                                        <p id="taskPriority" class="mb-2">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('task-assignments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Penugasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Update progress value display
function updateProgressValue(value) {
    document.getElementById('progressValue').textContent = value + '%';
}

// Load task information when selection changes
document.getElementById('job_responsibility_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        // Update priority display
        const priority = selectedOption.getAttribute('data-priority');
        const priorityMap = {
            'critical': '<span class="badge bg-danger">Kritis</span>',
            'high': '<span class="badge bg-warning">Tinggi</span>',
            'medium': '<span class="badge bg-info">Sedang</span>',
            'low': '<span class="badge bg-success">Rendah</span>'
        };
        document.getElementById('taskPriority').innerHTML = priorityMap[priority] || '<span class="badge bg-secondary">-</span>';
        
        // Show task name as description
        document.getElementById('taskDescription').textContent = selectedOption.text.split('(')[0].trim();
    } else {
        document.getElementById('taskPriority').innerHTML = '-';
        document.getElementById('taskDescription').textContent = 'Pilih tugas untuk melihat deskripsi';
    }
});

// Initialize progress display
updateProgressValue(document.getElementById('progress_percentage').value);
</script>
@endpush