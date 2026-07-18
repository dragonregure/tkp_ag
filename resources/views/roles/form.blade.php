@extends('layouts.admin')

@section('title', $role->exists ? 'Edit Role' : 'Tambah Role')
@section('page_title', $role->exists ? 'Edit Role' : 'Tambah Role')

@section('content')
    <form method="POST" action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}" class="card">
        @csrf
        @if ($role->exists)
            @method('PUT')
        @endif

        <div class="card-body">
            @include('roles.partials.fields')
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Simpan
            </button>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $('#permissions').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih permission'
        });
    </script>
@endpush
