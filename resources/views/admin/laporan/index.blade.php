@extends('layouts.main')

@section('title', 'Laporan Tracer Study')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">
                    <div class="card shadow-sm rounded-4 border border-dashed border-dark overflow-hidden">
                        <div class="card-body p-10 p-lg-15">

                            <div class="text-center mb-12">
                                <div class="mb-5">
                                    <span class="symbol symbol-100px">
                                        <span class="symbol-label bg-light-primary shadow-sm rounded-circle">
                                            <i class="fa-solid fa-file-word text-primary fs-3x"></i>
                                        </span>
                                    </span>
                                </div>
                                <h2 class="fs-1 fw-bolder text-gray-900 mb-3">Cetak Laporan Tracer Study</h2>
                                <div class="text-muted fs-5 fw-semibold">
                                    Pilih kriteria spesifik untuk menyusun laporan rekapitulasi ke dalam dokumen cetak.
                                </div>
                            </div>

                            <form id="form-export-laporan" action="{{ route('admin.laporan.export') }}" method="GET">

                                <div class="bg-light rounded-4 p-8 mb-10 border border-gray-200">
                                    <h4 class="text-gray-800 fw-bold mb-6 d-flex align-items-center">
                                        <i class="fa-solid fa-sliders text-muted me-2"></i> Parameter Filter
                                    </h4>

                                    <div class="row g-6">

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-gray-700 fs-7 mb-2">
                                                <i class="fa-solid fa-clipboard-list text-primary me-2"></i>Kuesioner
                                            </label>
                                            <select
                                                class="form-select form-select-sm form-select-solid fw-bold bg-white border border-gray-300"
                                                name="kuesioner_id" data-control="select2">
                                                <option value="all">Semua Kuesioner</option>
                                                @foreach ($kuesioner as $k)
                                                    <option value="{{ $k->id_kuesioner }}">{{ $k->judul }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-gray-700 fs-7 mb-2">
                                                <i class="fa-solid fa-calendar-alt text-success me-2"></i>Tahun Akademik
                                            </label>
                                            <select
                                                class="form-select form-select-sm form-select-solid fw-bold bg-white border border-gray-300"
                                                name="akademik_id" data-control="select2">
                                                <option value="all">Semua Tahun</option>
                                                @foreach ($tahunAkademik as $t)
                                                    <option value="{{ $t->id_smt }}">{{ $t->nm_smt }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @if($isFakultas)
                                            <input type="hidden" name="fakultas_id" id="laporan_fakultas" value="{{ $userFakultasId }}">
                                        @else
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-gray-700 fs-7 mb-2">
                                                <i class="fa-solid fa-building text-warning me-2"></i>Fakultas
                                            </label>
                                            <select
                                                class="form-select form-select-sm form-select-solid fw-bold bg-white border border-gray-300"
                                                name="fakultas_id" id="laporan_fakultas" data-control="select2">
                                                <option value="all">Semua Fakultas</option>
                                                @foreach ($fakultas as $f)
                                                    <option value="{{ $f->id_fakultas }}">{{ $f->nama_fakultas }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif

                                        <div class="{{ $isFakultas ? 'col-md-12' : 'col-md-6' }}">
                                            <label class="form-label fw-bold text-gray-700 fs-7 mb-2">
                                                <i class="fa-solid fa-graduation-cap text-info me-2"></i>Program Studi
                                            </label>
                                            <select
                                                class="form-select form-select-sm form-select-solid fw-bold bg-white border border-gray-300"
                                                name="prodi_id" id="laporan_prodi" data-control="select2">
                                                <option value="all">Semua Prodi</option>
                                                @foreach ($prodi as $p)
                                                    <option value="{{ $p->id_prodi }}"
                                                        data-fakultas="{{ $p->fakultas_id }}">{{ $p->jenjang }}
                                                        {{ $p->nama_prodi }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center">
                                    <button type="submit" id="btn-submit-laporan"
                                        class="btn btn-primary fw-bold px-8 py-3 w-100 shadow-sm hover-elevate-up">
                                        <span class="indicator-label">
                                            <i class="fa-solid fa-cloud-arrow-down me-2 fs-4"></i> Generate & Download Laporan
                                        </span>
                                        <span class="indicator-progress d-none">
                                            <span class="spinner-border spinner-border-sm align-middle me-2"></span> Menyusun & Mengunduh Laporan...
                                        </span>
                                    </button>
                                </div>
                            </form>
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
            var $prodiSelect = $('#laporan_prodi');

            var originalProdiOptions = $prodiSelect.find('option').clone();

            function toggleProdi() {
                var selectedFakultas = $('#laporan_fakultas').val();

                if (!selectedFakultas || selectedFakultas === 'all') {
                    $prodiSelect.empty().append(originalProdiOptions.clone());
                    $prodiSelect.prop('disabled', true);
                    $prodiSelect.val('all').trigger('change.select2');
                } else {
                    $prodiSelect.prop('disabled', false);

                    $prodiSelect.empty();
                    originalProdiOptions.each(function() {
                        var val = $(this).val();
                        var fakultas = $(this).data('fakultas');

                        if (val === 'all' || fakultas == selectedFakultas) {
                            $prodiSelect.append($(this).clone());
                        }
                    });

                    $prodiSelect.val('all').trigger('change.select2');
                }
            }

            toggleProdi();

            $('#laporan_fakultas').on('change', function() {
                toggleProdi();
            });

            $('#form-export-laporan').on('submit', async function(e) {
                e.preventDefault();

                var hasTemplate = {{ $hasCustomTemplate ? 'true' : 'false' }};

                if (!hasTemplate) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "File template laporan belum diunggah! Silakan kelola pada menu Template Laporan terlebih dahulu.",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti!",
                        customClass: {
                            confirmButton: "btn btn-warning"
                        }
                    });
                    return false;
                }

                var $btn = $('#btn-submit-laporan');
                var $label = $btn.find('.indicator-label');
                var $progress = $btn.find('.indicator-progress');

                $btn.prop('disabled', true);
                $label.addClass('d-none');
                $progress.removeClass('d-none');

                Swal.fire({
                    title: 'Memproses Laporan...',
                    text: 'Sistem sedang menyusun rekapitulasi data dan membuat dokumen Word. Mohon tunggu...',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                var url = $(this).attr('action');
                var queryString = $(this).serialize();
                var fullUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + queryString;

                try {
                    const response = await fetch(fullUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        let errorMsg = 'Gagal mengunduh laporan tracer study.';
                        try {
                            const errJson = await response.json();
                            if (errJson && errJson.message) {
                                errorMsg = errJson.message;
                            }
                        } catch (ignore) {}
                        throw new Error(errorMsg);
                    }

                    let filename = 'Laporan_Tracer_Study.docx';
                    const disposition = response.headers.get('content-disposition');
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        let matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1].replace(/['"]/g, '').trim();
                        }
                    }

                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    a.remove();

                    Swal.fire({
                        title: 'Berhasil Diunduh!',
                        text: 'Laporan Tracer Study (' + filename + ') berhasil dibuat dan diunduh ke perangkat Anda.',
                        icon: 'success',
                        buttonsStyling: false,
                        confirmButtonText: 'Selesai',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                } catch (err) {
                    console.error(err);
                    Swal.fire({
                        title: 'Gagal Mengunduh!',
                        text: err.message || 'Terjadi kesalahan saat memproses laporan.',
                        icon: 'error',
                        buttonsStyling: false,
                        confirmButtonText: 'Tutup',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        }
                    });
                } finally {
                    $btn.prop('disabled', false);
                    $label.removeClass('d-none');
                    $progress.addClass('d-none');
                }
            });
        });
    </script>
@endsection

