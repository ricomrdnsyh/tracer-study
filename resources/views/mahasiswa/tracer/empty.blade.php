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

                    <div class="card shadow-sm border border-dashed border-primary rounded text-center mb-5">
                        <div class="card-body p-10 p-lg-20">

                            <div class="mb-10 d-flex justify-content-center">
                                <div class="symbol symbol-150px symbol-circle shadow-sm">
                                    <div class="symbol-label bg-light-primary d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-clipboard-list text-primary" style="font-size: 5rem;"></i>
                                    </div>
                                </div>
                            </div>

                            <h1 class="text-gray-900 fw-bolder mb-5" style="font-size: 2.5rem;">Tracer Study</h1>

                            <div class="text-muted fs-4 fw-semibold mb-10 mx-auto" style="max-width: 600px; line-height: 1.8;">
                                {{ $message }}
                            </div>

                            <div class="d-flex justify-content-center">
                                <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-primary btn-lg px-8 fw-bold shadow-sm">
                                    <i class="fas fa-home me-2"></i> Kembali ke Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
@endsection

