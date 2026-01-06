@extends('layouts.app')

@section('title', 'Edit Penugasan')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit text-primary"></i> Edit Penugasan #{{ $assignment->id }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('task-assignments.update', $assignment->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Assignment Information -->
                            <div class="col-md-6 mb-3">
                                <label for="committee_id" class="form-label">Pengurus <span class="text-danger">*</span></label>
                                <select class="form-select @error('committee_id') is-invalid @enderror" 
                                        id="committee_id" name="committee_id" required>
                                    <option value="">Pilih Pengurus</option>
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}" 
                                            {{ old('committee_id', $assignment->committee_id) == $committee->id ? 'selected' : '' }}>
                                            {{ $committee->full_name }}
                                            @if($committee->position)
                                                - {{ $committee->position->name }}
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
                                            {{ old('job_responsibility_id', $assignment->job_responsibility_id) == $responsibility->id ? 'selected' : '' }}
                                            data-priority="{{ $responsibility->priority }}">
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
                            <div class="col-md-4 mb-3">
                                <label for="assigned_date" class="form-label">Tanggal Penugasan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('assigned_date') is-invalid @enderror" 
                                       id="assigned_date" name="assigned_date" 
                                       value="{{ old('assigned_date', $assignment->assigned_date->format('Y-m-d')) }}" required>
                                @error('assigned_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="due_date" class="form-label">Tanggal Deadline</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                       id="due_date" name="due_date" 
                                       value="{{ old('due_date', $assignment->due_date ? $assignment->due_date->format('Y-m-d') : '') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', $assignment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('status', $assignment->status) == 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                                    <option value="completed" {{ old('status', $assignment->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="overdue" {{ old('status', $assignment->status) == 'overdue' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="cancelled" {{ old('status', $assignment->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="progress_percentage" class="form-label">Progress (%)</label>
                                <input type="range" class="form-range" 
                                       id="progress_percentage" name="progress_percentage" 
                                       min="0" max="100" step="5" 
                                       value="{{ old('progress_percentage', $assignment->progress_percentage) }}"
                                       oninput="updateProgressValue(this.value)">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">0%</small>
                                    <span id="progressValue" class="fw-bold">{{ old('progress_percentage', $assignment->progress_percentage) }}%</span>
                                    <small class="text-muted">100%</small>
                                </div>
                                @error('progress_percentage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="completed_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control @error('completed_date') is-invalid @enderror" 
                                       id="completed_date" name="completed_date" 
                                       value="{{ old('completed_date', $assignment->completed_date ? $assignment->completed_date->format('Y-m-d') : '') }}">
                                @error('completed_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Hanya isi jika status "Selesai"</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $assignment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($assignment->status == 'completed' || old('status') == 'completed')
                        <div class="mb-3">
                            <label for="approved_by" class="form-label">Disetujui Oleh</label>
                            <select class="form-select @error('approved_by') is-invalid @enderror" 
                                    id="approved_by" name="approved_by">
                                <option value="">Pilih Pengurus</option>
                                @foreach($approvers as $approver)
                                    <option value="{{ $approver->id }}" 
                                        {{ old('approved_by', $assignment->approved_by) == $approver->id ? 'selected' : '' }}>
                                        {{ $approver->full_name }}
                                        @if($approver->position)
                                            - {{ $approver->position->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('approved_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih pengurus yang menyetujui tugas ini</small>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('task-assignments.show', $assignment->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
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
function updateProgressValue(value) {
    document.getElementById('progressValue').textContent = value + '%';
}

// Auto-update completed date when status changes to completed
document.getElementById('status').addEventListener('change', function() {
    if (this.value === 'completed') {
        const today = new Date().toISOString().split('T')[0];
        const completedDateField = document.getElementById('completed_date');
        if (!completedDateField.value) {
            completedDateField.value = today;
        }
    }
});
</script>
@endpush