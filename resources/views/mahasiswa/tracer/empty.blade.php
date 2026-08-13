@extends('layouts.main')

@section('title', 'Tracer Study')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">
                    
                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                            <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-success">Berhasil</h4>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="card shadow-sm text-center">
                        <div class="card-body p-lg-17">
                            <div class="mb-10">
                                <i class="fas fa-clipboard-check text-primary" style="font-size: 5rem;"></i>
                            </div>
                            <h1 class="fs-2hx text-dark mb-6">Tracer Study</h1>
                            <div class="fs-4 text-gray-500 mb-10">
                                {{ $message }}
                            </div>
                            <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-primary">Kembali ke Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
@endsection
