@extends('layouts.main')

@section('title', 'Tracer Study')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                <div class="card shadow-sm border border-dashed border-success rounded mb-5">
                    <div class="card-body p-10 p-lg-20 text-center">
                        
                        <!-- Icon Container -->
                        <div class="mb-10 d-flex justify-content-center">
                            <div class="symbol symbol-150px symbol-circle shadow-sm">
                                <div class="symbol-label bg-light-success d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-check-double text-success" style="font-size: 5rem;"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Text -->
                        <h1 class="text-gray-900 fw-bolder mb-5" style="font-size: 2.5rem;">Terima Kasih!</h1>
                        
                        <div class="text-muted fs-4 fw-semibold mb-10 mx-auto" style="max-width: 650px; line-height: 1.8;">
                            Anda telah berhasil berpartisipasi dalam mengisi kuesioner <br/>
                            <span class="text-gray-800 fw-bold">{{ $kuesioner->judul }}</span>.
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-center gap-4 flex-wrap">
                            <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-secondary btn-lg px-8 fw-bold shadow-sm">
                                <i class="fas fa-home me-2"></i> Kembali ke Dashboard
                            </a>
                            <a href="{{ route('mahasiswa.tracer.index', ['edit' => 'true']) }}" class="btn btn-success btn-lg px-8 fw-bold shadow-sm">
                                <i class="fas fa-edit me-2"></i> Edit Jawaban Saya
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
