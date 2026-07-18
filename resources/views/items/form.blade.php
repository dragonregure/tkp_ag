@extends('layouts.admin')

@section('title', $item->exists ? 'Edit Item' : 'Tambah Item')
@section('page_title', $item->exists ? 'Edit Item' : 'Tambah Item')

@section('content')
    <form method="POST" action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}" enctype="multipart/form-data" class="card">
        @csrf
        @if ($item->exists)
            @method('PUT')
        @endif

        <div class="card-body">
            @include('items.partials.fields')
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Simpan
            </button>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection
