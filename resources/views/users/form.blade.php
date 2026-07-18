@extends('layouts.admin')

@section('title', $user->exists ? 'Edit User' : 'Tambah User')
@section('page_title', $user->exists ? 'Edit User' : 'Tambah User')

@section('content')
    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="card">
        @csrf
        @if ($user->exists)
            @method('PUT')
        @endif

        <div class="card-body">
            @include('users.partials.fields')
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Simpan
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $('#roles').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih role'
        });
    </script>
@endpush
