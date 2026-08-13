@extends('layouts.main')

@section('title', 'Tracer Study')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                <div class="card shadow-sm border-0 mb-5">
                    <div class="card-body p-10 text-center">
                        <div class="mb-5">
                            <i class="fa-solid fa-clipboard-check text-success" style="font-size: 80px;"></i>
                        </div>
                        <h2 class="text-dark fw-bolder mb-3">Terima Kasih!</h2>
                        <div class="text-muted fs-5 fw-bold mb-7">
                            Anda sudah berpartisipasi mengisi <strong>{{ $kuesioner->judul }}</strong> pada periode <strong>{{ $periodeAktif->nama_periode }}</strong>.
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                            </a>
                            <a href="{{ route('mahasiswa.tracer.index', ['edit' => 'true']) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Edit Jawaban
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
@endsection

@section('js')
    @if (session('success'))
        <script>
            Swal.fire({
                text: "{{ session('success') }}",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, Mengerti!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        </script>
    @endif
@endsection
