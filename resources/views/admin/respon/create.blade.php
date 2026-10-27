@extends('layouts.main')

@section('title', 'Tambah Responden Manual')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    @if (session('error'))
                        <div class="alert alert-danger p-5 mb-5">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger p-5 mb-5">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div
                        class="card shadow-sm border border-dashed border-primary rounded mb-5 overflow-hidden position-relative">

                        <div
                            class="d-flex flex-wrap justify-content-between align-items-center px-6 px-lg-8 py-4 border-bottom border-gray-200 bg-light-primary bg-opacity-40 rounded-top">
                            <div class="d-flex align-items-center gap-3 my-1">
                                <a href="{{ route('admin.respon.index') }}"
                                    class="btn btn-icon btn-sm btn-light-primary shadow-sm" data-bs-toggle="tooltip"
                                    title="Kembali ke Daftar Respon">
                                    <i class="fas fa-arrow-left fs-5"></i>
                                </a>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="badge badge-light-primary fw-bolder fs-7 text-uppercase px-3 py-2 rounded-pill me-2 border border-primary border-opacity-20">
                                        <i class="fas fa-user-plus text-primary me-2 fs-6"></i>Tambah Responden
                                    </span>
                                    <span class="text-gray-600 fs-7 fw-bold d-none d-sm-inline">Isi jawaban kuesioner atas
                                        nama mahasiswa yang dipilih</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 my-1">
                                <span
                                    class="badge {{ $kuesionerAktif && $mahasiswaTarget ? 'badge-light-secondary text-muted border' : 'badge-primary' }} fw-bold fs-8 px-3 py-2">
                                    <i class="fas fa-filter me-1"></i>1. Pilih Sasaran
                                </span>
                                <span
                                    class="badge {{ $kuesionerAktif && $mahasiswaTarget ? 'badge-primary' : 'badge-light-secondary text-muted border' }} fw-bold fs-8 px-3 py-2">
                                    <i class="fas fa-clipboard-list me-1"></i>2. Isi Jawaban
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row g-5">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold required">Kuesioner</label>
                                    <select id="picker_kuesioner" class="form-select form-select-sm border-gray-300"
                                        data-control="select2" data-placeholder="Pilih Kuesioner">
                                        <option value=""></option>
                                        @foreach ($kuesionerList as $k)
                                            <option value="{{ $k->id_kuesioner }}"
                                                {{ optional($kuesionerAktif)->id_kuesioner == $k->id_kuesioner ? 'selected' : '' }}>
                                                {{ $k->judul }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold required">Mahasiswa</label>
                                    <select id="picker_mahasiswa" class="form-select form-select-sm border-gray-300"
                                        data-placeholder="Cari NIM / nama mahasiswa...">
                                        @if ($mahasiswaTarget)
                                            <option value="{{ $mahasiswaTarget->nim }}" selected>
                                                {{ $mahasiswaTarget->nim }} -
                                                {{ $mahasiswaTarget->nama }}{{ $mahasiswaTarget->prodi ? ' (' . $mahasiswaTarget->prodi->nama_prodi . ')' : '' }}
                                            </option>
                                        @endif
                                    </select>
                                    <div class="form-text">Daftar disaring mengikuti tahun akademik kuesioner yang dipilih.
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold d-block">&nbsp;</label>
                                    <button type="button" id="btn_tampilkan" class="btn btn-primary btn-sm w-100">
                                        <span class="indicator-label"><i class="fas fa-clipboard-list me-2"></i>Tampilkan
                                            Form Kuesioner</span>
                                        <span class="indicator-progress" style="display: none;">
                                            Memproses... <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($kuesionerAktif && $mahasiswaTarget)
                        <div class="card shadow-sm border border-dashed border-dark rounded mb-5">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">
                                        <i class="fas fa-clipboard-list text-dark me-2"></i>Form Jawaban
                                    </span>
                                    <span class="text-muted mt-1 fw-bold fs-7">
                                        {{ $kuesionerAktif->judul }} — {{ $mahasiswaTarget->nama }}
                                        ({{ $mahasiswaTarget->nim }})
                                    </span>
                                </h3>
                            </div>
                            <div class="separator mt-0 mb-0"></div>
                            <div class="card-body pt-6">
                                <div
                                    class="alert alert-dismissible bg-light-info border border-info border-dashed d-flex align-items-center p-3 mb-5">
                                    <i class="fa-solid fa-circle-info fs-2 text-info me-3"></i>
                                    <div class="d-flex flex-column">
                                        <span class="fs-7 text-gray-800">
                                            Mengisi atas nama <strong>{{ $mahasiswaTarget->nama }}
                                                ({{ $mahasiswaTarget->nim }})</strong>
                                            untuk kuesioner <strong>{{ $kuesionerAktif->judul }}</strong>.
                                            @if ($errors->isEmpty() && empty($jawabanUser))
                                                Belum ada jawaban tersimpan.
                                            @else
                                                Jawaban yang sudah tersimpan dimuat dan dapat diubah.
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <form action="{{ route('admin.respon.store') }}" method="POST" id="form_tracer_admin" novalidate>
                                    @csrf
                                    <input type="hidden" name="kuesioner_id" value="{{ $kuesionerAktif->id_kuesioner }}">
                                    <input type="hidden" name="nim" value="{{ $mahasiswaTarget->nim }}">

                                    @php
                                        $kuesioner = $kuesionerAktif;
                                    @endphp
                                    @include('mahasiswa.tracer._fields')

                                    <div class="separator mb-5"></div>
                                    <div class="d-flex justify-content-end mb-5">
                                        <button type="button" id="btn_submit_tracer" class="btn btn-success btn-md w-100">
                                            <i class="fas fa-paper-plane me-2"></i>Simpan Jawaban
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
@endsection

@section('js')
    @include('mahasiswa.tracer._form_js', ['tracerLookupBase' => route('admin.respon.lookup')])
    <script>
        $(function() {
            $('#picker_mahasiswa').select2({
                placeholder: 'Cari NIM / nama mahasiswa...',
                allowClear: true,
                ajax: {
                    url: "{{ route('admin.respon.mahasiswa-search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            kuesioner_id: $('#picker_kuesioner').val() || ''
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data || []
                        };
                    },
                    cache: true
                }
            });

            $('#picker_kuesioner').on('change', function() {
                $('#picker_mahasiswa').val(null).trigger('change');
            });

            $('#btn_tampilkan').on('click', function() {
                var k = $('#picker_kuesioner').val();
                var n = $('#picker_mahasiswa').val();
                if (!k || !n) {
                    Swal.fire({
                        text: 'Pilih kuesioner dan mahasiswa terlebih dahulu.',
                        icon: 'warning',
                        confirmButtonText: 'Mengerti'
                    });
                    return;
                }
                var btn = $('#btn_tampilkan');
                btn.attr('data-kt-indicator', 'on');
                btn.find('.indicator-label').hide();
                btn.find('.indicator-progress').show();
                btn.prop('disabled', true);
                window.location = "{{ route('admin.respon.create') }}?kuesioner_id=" +
                    encodeURIComponent(k) + "&nim=" + encodeURIComponent(n);
            });
        });
    </script>
@endsection
