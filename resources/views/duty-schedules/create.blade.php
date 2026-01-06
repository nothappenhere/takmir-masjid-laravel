@extends('layouts.app')

@section('title', 'Tambah Jadwal Baru')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-plus text-primary"></i> Tambah Jadwal Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('duty-schedules.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
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
                                        </option>
                                    @endforeach
                                </select>
                                @error('committee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duty_type" class="form-label">Jenis Tugas <span class="text-danger">*</span></label>
                                <select class="form-select @error('duty_type') is-invalid @enderror" 
                                        id="duty_type" name="duty_type" required>
                                    <option value="">Pilih Jenis Tugas</option>
                                    <option value="piket" {{ old('duty_type') == 'piket' ? 'selected' : '' }}>Piket</option>
                                    <option value="kebersihan" {{ old('duty_type') == 'kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                                    <option value="keamanan" {{ old('duty_type') == 'keamanan' ? 'selected' : '' }}>Keamanan</option>
                                    <option value="administrasi" {{ old('duty_type') == 'administrasi' ? 'selected' : '' }}>Administrasi</option>
                                    <option value="lainnya" {{ old('duty_type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('duty_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date & Time -->
                            <div class="col-md-6 mb-3">
                                <label for="duty_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('duty_date') is-invalid @enderror" 
                                       id="duty_date" name="duty_date" 
                                       value="{{ old('duty_date', $defaultDate) }}" required>
                                @error('duty_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" 
                                       value="{{ old('start_time', $defaultStartTime) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time" 
                                       value="{{ old('end_time', $defaultEndTime) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Location & Status -->
                            <div class="col-md-8 mb-3">
                                <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" 
                                       value="{{ old('location') }}" 
                                       placeholder="Contoh: Gedung Utama, Ruang Sholat, Tempat Wudhu" required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Sedang Berjalan</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Recurring Options -->
                            <div class="col-12 mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_recurring" name="is_recurring" value="1"
                                           {{ old('is_recurring') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_recurring">
                                        Jadwal Berulang
                                    </label>
                                </div>
                                
                                <div id="recurringOptions" style="{{ old('is_recurring') ? '' : 'display: none;' }}">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="recurring_type" class="form-label">Jenis Pengulangan</label>
                                            <select class="form-select" id="recurring_type" name="recurring_type">
                                                <option value="">Pilih Jenis</option>
                                                <option value="daily" {{ old('recurring_type') == 'daily' ? 'selected' : '' }}>Harian</option>
                                                <option value="weekly" {{ old('recurring_type') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                                <option value="monthly" {{ old('recurring_type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label for="recurring_end_date" class="form-label">Tanggal Berakhir</label>
                                            <input type="date" class="form-control" 
                                                   id="recurring_end_date" name="recurring_end_date"
                                                   value="{{ old('recurring_end_date') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="col-12 mb-3">
                                <label for="remarks" class="form-label">Catatan</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          id="remarks" name="remarks" rows="3"
                                          placeholder="Catatan tambahan tentang jadwal ini">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('duty-schedules.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Jadwal
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
document.getElementById('is_recurring').addEventListener('change', function() {
    document.getElementById('recurringOptions').style.display = this.checked ? 'block' : 'none';
});
</script>
@endpush