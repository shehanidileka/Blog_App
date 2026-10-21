@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/panel.css') }}?v={{ filemtime(public_path('css/panel.css')) }}" rel="stylesheet">
@endpush

@section('content')
<div class="row g-4 panel-wrap">
    <div class="col-lg-3">
        @include('partials.panel-sidebar')
    </div>

    <div class="col-lg-9">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 py-2" role="alert">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger rounded-3 py-2">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        @yield('panel')
    </div>
</div>
@endsection