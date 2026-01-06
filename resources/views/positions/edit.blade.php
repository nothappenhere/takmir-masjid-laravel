@extends('layouts.app')

@section('title', 'Edit Data Jabatan')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit text-warning"></i> Edit Data Jabatan
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('positions.update', $position->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $position->name) }}" required
                                       placeholder="Contoh: Ketua Takmir, Sekretaris, Bendahara">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="order" class="form-label">Urutan</label>
                                <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                       id="order" name="order" value="{{ old('order', $position->order) }}"
                                       min="0" placeholder="0">
                                <div class="form-text">Semakin kecil angkanya, semakin atas posisinya</div>
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3"
                                          placeholder="Deskripsi tugas dan tanggung jawab jabatan ini">{{ old('description', $position->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Hierarchy -->
                            <div class="col-md-6 mb-3">
                                <label for="parent_id" class="form-label">Jabatan Atasan</label>
                                <select class="form-select @error('parent_id') is-invalid @enderror" 
                                        id="parent_id" name="parent_id">
                                    <option value="">Tidak ada atasan (Root)</option>
                                    @foreach($positions as $pos)
                                        @if($pos->id != $position->id)
                                        <option value="{{ $pos->id }}" 
                                            {{ old('parent_id', $position->parent_id) == $pos->id ? 'selected' : '' }}>
                                            {{ $pos->name }}
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih jabatan yang menjadi atasan langsung</div>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Level & Status -->
                            <div class="col-md-6 mb-3">
                                <label for="level" class="form-label">Level Jabatan <span class="text-danger">*</span></label>
                                <select class="form-select @error('level') is-invalid @enderror" 
                                        id="level" name="level" required>
                                    <option value="">Pilih Level</option>
                                    <option value="leadership" {{ old('level', $position->level) == 'leadership' ? 'selected' : '' }}>Kepemimpinan</option>
                                    <option value="division_head" {{ old('level', $position->level) == 'division_head' ? 'selected' : '' }}>Kepala Divisi</option>
                                    <option value="staff" {{ old('level', $position->level) == 'staff' ? 'selected' : '' }}>Staf</option>
                                    <option value="volunteer" {{ old('level', $position->level) == 'volunteer' ? 'selected' : '' }}>Relawan</option>
                                </select>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="active" {{ old('status', $position->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ old('status', $position->status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('positions.show', $position->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection