@extends('layouts.main')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Dashboard Admin</h1>
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2>Selamat Datang di Dashboard Admin</h2>
                    <p>Anda login sebagai Admin. Anda memiliki akses ke seluruh data Tracer Study.</p>
                    
                    <div class="row g-5 g-xl-8 mt-5">
                        <div class="col-xl-3">
                            <div class="card bg-primary hoverable card-xl-stretch mb-xl-8">
                                <div class="card-body">
                                    <i class="fas fa-users text-white fs-3x ms-n1"></i>
                                    <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $stats['total_mahasiswa'] }}</div>
                                    <div class="fw-semibold text-white">Total Mahasiswa</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="card bg-success hoverable card-xl-stretch mb-xl-8">
                                <div class="card-body">
                                    <i class="fas fa-building text-white fs-3x ms-n1"></i>
                                    <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $stats['total_fakultas'] }}</div>
                                    <div class="fw-semibold text-white">Total Fakultas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="card bg-info hoverable card-xl-stretch mb-xl-8">
                                <div class="card-body">
                                    <i class="fas fa-graduation-cap text-white fs-3x ms-n1"></i>
                                    <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $stats['total_prodi'] }}</div>
                                    <div class="fw-semibold text-white">Total Prodi</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="card bg-warning hoverable card-xl-stretch mb-xl-8">
                                <div class="card-body">
                                    <i class="fas fa-clipboard-check text-white fs-3x ms-n1"></i>
                                    <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $stats['total_responden'] }}</div>
                                    <div class="fw-semibold text-white">Total Responden Tracer</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
