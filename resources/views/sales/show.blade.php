@extends('layouts.admin')

@section('title', 'Detail Penjualan')
@section('page_title', 'Detail Penjualan')

@section('content')
    <div class="row">
        <div class="col-lg-5">
            @include('sales.partials.detail-card')
        </div>
        <div class="col-lg-7">
            @include('sales.partials.detail-items')
            @include('sales.partials.detail-payments')
        </div>
    </div>
@endsection
