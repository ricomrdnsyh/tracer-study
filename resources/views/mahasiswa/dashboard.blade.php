@extends('layouts.main')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Dashboard Mahasiswa</h1>
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2>Selamat Datang, {{ Auth::guard('mahasiswa')->user()->nama }}</h2>
                    <p>Anda telah login sebagai Mahasiswa. Silakan isi kuesioner Tracer Study yang tersedia.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
