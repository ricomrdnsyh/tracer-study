@extends('layouts.main')

@section('title', 'Template Laporan')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">


                    <div class="row g-5 g-xl-10">
                        <!-- Card Kiri: Status -->
                        <div class="col-xl-4">
                            <div class="card shadow-sm bg-light-primary rounded-4 border border-dashed border-primary">
                                <div class="card-body p-8 d-flex flex-column justify-content-center text-center h-100">
                                    <div class="mb-6 mt-4">
                                        <i class="fa-solid fa-file-word text-primary"
                                            style="font-size: 5rem; text-shadow: 0 4px 15px rgba(0, 158, 247, 0.3);"></i>
                                    </div>
                                    <h3 class="fs-2 fw-bolder text-gray-900 mb-2">Template Saat Ini</h3>
                                    <div class="mb-8 mt-2">
                                        @if ($hasCustomTemplate)
                                            <span class="badge badge-success fs-7 px-4 py-2 shadow-sm">
                                                <i class="fa-solid fa-circle-check text-white me-2"></i>Template Tersedia
                                            </span>
                                        @else
                                            <span class="badge badge-danger fs-7 px-4 py-2 shadow-sm">
                                                <i class="fa-solid fa-triangle-exclamation text-white me-2"></i>Belum Ada Template
                                            </span>
                                        @endif
                                    </div>
                                    @if ($hasCustomTemplate)
                                        <a href="{{ route('admin.template-laporan.download') }}"
                                            class="btn btn-primary fw-bolder w-100 py-3 mt-auto hover-elevate-up transition">
                                            <i class="fa-solid fa-download me-2"></i>Lihat Template
                                        </a>
                                    @else
                                        <button disabled class="btn btn-secondary fw-bolder w-100 py-3 mt-auto">
                                            <i class="fa-solid fa-download me-2"></i>Template Kosong
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Card Kanan: Upload -->
                        <div class="col-xl-8">
                            <div class="card shadow-sm rounded-4 border border-dashed border-dark">
                                <div class="card-header border-0 pt-8 px-8">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder fs-2 mb-1 d-flex align-items-center">
                                            <i class="fa-solid fa-file-arrow-up text-primary fs-2 me-3"></i>
                                            Unggah Template Baru
                                        </span>
                                        <span class="text-muted fs-6 fw-semibold mt-1">Perbarui template laporan untuk
                                            format rekapitulasi Anda</span>
                                    </h3>
                                </div>
                                <div class="card-body p-8">
                                    <form action="{{ route('admin.template-laporan.upload') }}" method="POST"
                                        enctype="multipart/form-data" id="upload_template_form">
                                        @csrf
                                        <div class="mb-8">
                                            <!-- Custom File Input -->
                                            <div class="border border-dashed border-primary rounded-4 p-8 text-center bg-light-primary hover-elevate-up transition"
                                                style="cursor: pointer;"
                                                onclick="document.getElementById('template_file').click()">
                                                <i class="fa-solid fa-cloud-arrow-up fs-3x text-primary mb-3"></i>
                                                <h4 class="fw-bolder text-gray-800 mb-1">Klik di Sini untuk Memilih File
                                                </h4>
                                                <span class="text-muted fs-7">Hanya mendukung dokumen MS Word (.docx) dengan
                                                    ukuran maksimal 10MB</span>
                                                <input type="file" id="template_file" name="template_file" accept=".docx"
                                                    class="d-none" required
                                                    onchange="document.getElementById('file_name').innerHTML = '<i class=\'fa-solid fa-check-circle me-2\'></i>' + this.files[0].name">
                                                <div id="file_name" class="mt-4 fw-bolder text-success fs-5"></div>
                                            </div>
                                        </div>

                                        <div class="bg-light-info rounded-4 p-6 mb-8 border border-info border-dashed">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fa-solid fa-circle-info text-info fs-3 me-3"></i>
                                                <h5 class="text-info fw-bolder mb-0">Daftar Variabel Valid</h5>
                                            </div>
                                            <p class="text-gray-700 fs-7 mb-4">Pastikan Anda menyalin persis kode variabel
                                                di bawah ini ke dalam dokumen Word. Sistem otomatis akan mengganti teks ini
                                                dengan data statistik.</p>
                                            <div class="d-flex flex-wrap gap-2">
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Dekan}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Jenjang}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Kaprodi}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Responden}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Persentase}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Lulusan}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Bekerja}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Wiraswasta}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Melanjutkan Studi}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Pendapatan Kerja}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Rata-rata Waktu Tunggu}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Median Waktu Tunggu}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Keselarasan Horizontal}</code></span>
                                                <span
                                                    class="badge bg-white text-dark fw-bold px-3 py-2 border border-gray-300 shadow-sm"><code
                                                        class="text-primary">${Keselarasan Vertikal}</code></span>
                                            </div>
                                        </div>

                                        <button type="submit" id="btn_submit_template"
                                            class="btn btn-primary fw-bolder fs-5 w-100 py-4 hover-elevate-up shadow-sm">
                                            <span class="indicator-label"><i class="fa-solid fa-rocket me-2"></i>Simpan dan Gunakan Template Baru</span>
                                            <span class="indicator-progress">
                                                Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            @include('layouts.footer')
        </div>
    </div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // SweetAlert Flash Messages
        @if(session('success'))
            Swal.fire({
                text: "{{ session('success') }}",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, Mengerti!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                text: "{{ session('error') }}",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, Mengerti!",
                customClass: {
                    confirmButton: "btn btn-danger"
                }
            });
        @endif

        @if($errors->any())
            Swal.fire({
                text: "{{ $errors->first() }}",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, Mengerti!",
                customClass: {
                    confirmButton: "btn btn-danger"
                }
            });
        @endif

        // Form Submit Validation & Processing
        $('#upload_template_form').on('submit', function(e) {
            var fileInput = document.getElementById('template_file');
            var filePath = fileInput.value;
            var allowedExtensions = /(\.docx)$/i;

            if (!filePath) {
                e.preventDefault();
                Swal.fire({
                    text: "Silakan pilih file template terlebih dahulu.",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, Mengerti!",
                    customClass: {
                        confirmButton: "btn btn-warning"
                    }
                });
                return false;
            }

            if (!allowedExtensions.exec(filePath)) {
                e.preventDefault();
                Swal.fire({
                    text: "Format file tidak valid! Harap unggah file dengan ekstensi .docx",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, Mengerti!",
                    customClass: {
                        confirmButton: "btn btn-danger"
                    }
                });
                fileInput.value = '';
                document.getElementById('file_name').innerHTML = '';
                return false;
            }

            // Show Processing Indicator
            var btn = document.querySelector("#btn_submit_template");
            btn.setAttribute("data-kt-indicator", "on");
            setTimeout(function() {
                btn.setAttribute("disabled", "disabled");
            }, 10);
        });
    });
</script>
@endsection
