@extends('layouts.main')

@section('title', 'Form Tracer Study')

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

                    <div class="card shadow-sm border border-dashed border-primary rounded mb-5">
                        <div class="card-header pt-6 border-0">
                            <h3 class="card-title m-0 d-flex align-items-start">
                                <div class="me-4 d-flex justify-content-center align-items-center mt-1">
                                    <i class="fas fa-clipboard-list text-primary fs-1"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="card-label fw-bolder fs-2 m-0 mb-2">{{ $kuesioner->judul }}</span>
                                    <span class="text-muted fw-bold fs-7 d-flex align-items-center m-0">
                                        Masa Berlaku:
                                        {{ $kuesioner->tgl_mulai ? \Carbon\Carbon::parse($kuesioner->tgl_mulai)->format('d M') . ' - ' . \Carbon\Carbon::parse($kuesioner->tgl_selesai)->format('d M Y') : 'Tanpa batas waktu' }}
                                    </span>
                                </div>
                            </h3>
                        </div>
                        <div class="separator my-2"></div>
                        <div class="card-body">
                            <form action="{{ route('mahasiswa.tracer.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="kuesioner_id" value="{{ $kuesioner->id_kuesioner }}">

                                @foreach ($kuesioner->kategoriPertanyaans as $kategori)
                                    <div class="kategori-container"
                                        data-syarat-pertanyaan="{{ $kategori->syarat_pertanyaan_id }}"
                                        data-syarat-jawaban='@json($kategori->syarat_jawaban)'>

                                        <div class="row">
                                            @foreach ($kategori->pertanyaans as $pertanyaan)
                                                @php
                                                    $isKompetensi =
                                                        str_contains(
                                                            strtolower($kategori->nama_kategori),
                                                            'kompetensi',
                                                        ) ||
                                                        str_contains(
                                                            strtolower($kategori->nama_kategori),
                                                            'metode pembelajaran',
                                                        );
                                                    $colClass = $isKompetensi ? 'col-12 col-md-6' : 'col-12';
                                                @endphp
                                                <div class="{{ $colClass }} mb-6 pertanyaan-container"
                                                    data-syarat-pertanyaan="{{ $pertanyaan->syarat_pertanyaan_id }}"
                                                    data-syarat-jawaban='@json($pertanyaan->syarat_jawaban)'>
                                                    @php
                                                        $answer = $jawabanUser[$pertanyaan->id_pertanyaan] ?? null;
                                                    @endphp
                                                    <label
                                                        class="form-label fs-6 fw-bold text-gray-800 {{ $pertanyaan->wajib ? 'required' : '' }} mb-2">
                                                        [{{ strtoupper($pertanyaan->kode_pertanyaan) }}]
                                                        {{ $pertanyaan->teks_pertanyaan }}
                                                    </label>

                                                    @if ($pertanyaan->tipe_jawaban == 'text')
                                                        @if (in_array($pertanyaan->kode_pertanyaan, ['f5a1', 'f5a2', 'f18b', 'f18c']))
                                                            <div class="position-relative"
                                                                data-remote-select="{{ $pertanyaan->kode_pertanyaan }}"
                                                                data-disabled="{{ in_array($pertanyaan->kode_pertanyaan, ['f5a2', 'f18c']) ? 'true' : 'false' }}">
                                                                <input type="hidden"
                                                                    name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                    value="{{ $answer }}"
                                                                    {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                                <input type="search" data-remote-select-search
                                                                    name="jawaban_label[{{ $pertanyaan->id_pertanyaan }}]"
                                                                    class="form-control form-control-sm border-gray-300"
                                                                    placeholder="Ketik untuk mencari..." autocomplete="off"
                                                                    value="{{ $jawabanLabel[$pertanyaan->id_pertanyaan] ?? '' }}"
                                                                    {{ in_array($pertanyaan->kode_pertanyaan, ['f5a2', 'f18c']) ? 'readonly' : '' }}>
                                                                <div class="position-absolute w-100 mt-1 d-none overflow-hidden rounded border border-gray-300 bg-white shadow-sm"
                                                                    data-remote-select-results
                                                                    style="max-height: 200px; overflow-y: auto; z-index: 1000;">
                                                                </div>
                                                                <div class="form-text mt-2 text-muted"
                                                                    data-remote-select-status>Mulai ketik untuk mencari...
                                                                </div>
                                                            </div>
                                                        @else
                                                            <input type="text"
                                                                class="form-control form-control-sm border-gray-300"
                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                placeholder="Ketik jawaban Anda di sini..."
                                                                value="{{ $answer }}"
                                                                {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                        @endif
                                                    @elseif($pertanyaan->tipe_jawaban == 'textarea')
                                                        <textarea class="form-control form-control-sm border-gray-300" name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            rows="3" placeholder="Ketik jawaban Anda di sini..." {{ $pertanyaan->wajib ? 'required' : '' }}>{{ $answer }}</textarea>
                                                    @elseif($pertanyaan->tipe_jawaban == 'radio')
                                                        <div class="row g-3">
                                                            @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                                <div
                                                                    class="{{ $isKompetensi ? 'col' : 'col-12 col-md-6' }}">
                                                                    <label
                                                                        class="d-flex {{ $isKompetensi ? 'flex-column justify-content-center align-items-center' : 'align-items-center' }} border border-dashed border-gray-300 rounded {{ $isKompetensi ? 'p-2' : 'px-4 py-2' }} bg-hover-light cursor-pointer transition-base"
                                                                        style="transition: all 0.2s ease; height: 100%;">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-primary form-check-sm {{ $isKompetensi ? 'mb-2' : 'me-3' }}">
                                                                            <input
                                                                                class="form-check-input {{ $isKompetensi ? 'mx-auto' : '' }}"
                                                                                type="radio" value="{{ $opsi }}"
                                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                                {{ $answer == $opsi ? 'checked' : '' }}
                                                                                {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                                        </div>
                                                                        <span
                                                                            class="text-gray-700 fw-semibold fs-7 {{ $isKompetensi ? 'text-center mt-1' : 'text-start' }}">
                                                                            {{ $opsi }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($pertanyaan->tipe_jawaban == 'checkbox')
                                                        <div class="row g-3">
                                                            @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                                <div
                                                                    class="{{ $isKompetensi ? 'col' : 'col-12 col-md-6' }}">
                                                                    <label
                                                                        class="d-flex {{ $isKompetensi ? 'flex-column justify-content-center align-items-center' : 'align-items-center' }} border border-dashed border-gray-300 rounded {{ $isKompetensi ? 'p-2' : 'px-4 py-2' }} bg-hover-light cursor-pointer transition-base"
                                                                        style="transition: all 0.2s ease; height: 100%;">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-primary form-check-sm {{ $isKompetensi ? 'mb-2' : 'me-3' }}">
                                                                            <input
                                                                                class="form-check-input check-group-{{ $pertanyaan->id_pertanyaan }} {{ $isKompetensi ? 'mx-auto' : '' }}"
                                                                                type="checkbox" value="{{ $opsi }}"
                                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}][]"
                                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                                {{ is_array($answer) && in_array($opsi, $answer) ? 'checked' : '' }}>
                                                                        </div>
                                                                        <span
                                                                            class="text-gray-700 fw-semibold fs-7 {{ $isKompetensi ? 'text-center mt-1' : 'text-start' }}">
                                                                            {{ $opsi }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($pertanyaan->tipe_jawaban == 'select')
                                                        <select class="form-select form-select-sm border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            data-control="select2" data-placeholder="Pilih Jawaban"
                                                            {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                            <option value=""></option>
                                                            @foreach ($pertanyaan->opsi_jawaban as $opsi)
                                                                <option value="{{ $opsi }}"
                                                                    {{ $answer == $opsi ? 'selected' : '' }}>
                                                                    {{ $opsi }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($pertanyaan->tipe_jawaban == 'date')
                                                        <div class="position-relative w-100">
                                                            <div
                                                                class="position-absolute translate-middle-y top-50 start-0 ms-4">
                                                                <i class="fas fa-calendar-alt text-gray-500"></i>
                                                            </div>
                                                            <input type="text"
                                                                class="form-control form-control-sm border-gray-300 datepicker-input ps-12"
                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                placeholder="Pilih Tanggal"
                                                                value="{{ $answer ? date('Y-m-d', strtotime($answer)) : '' }}"
                                                                {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                        </div>
                                                    @elseif($pertanyaan->tipe_jawaban == 'number')
                                                        <input type="number"
                                                            class="form-control form-control-sm border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            placeholder="Masukkan angka..." value="{{ $answer }}"
                                                            {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <div class="separator mb-5"></div>
                                <div class="d-flex justify-content-end mb-5">
                                    <button type="button" id="btn_submit_tracer" class="btn btn-success btn-md w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Jawaban Tracer
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
    <script src="{{ asset('assets/js/tracer-lookup.js') }}"></script>
    <style>
        .skip-hidden {
            display: none !important;
        }
    </style>
    <script>
        $(document).ready(function() {
            // Init flatpickr
            if ($.fn.flatpickr) {
                $('.datepicker-input').flatpickr({
                    dateFormat: "Y-m-d",
                });
            } else if (typeof flatpickr !== 'undefined') {
                flatpickr('.datepicker-input', {
                    dateFormat: "Y-m-d",
                });
            } else {
                $('.datepicker-input').attr('type', 'date'); // Fallback
            }

            function getQuestionValue(id) {
                let $inputs = $('[name="jawaban[' + id + ']"], [name="jawaban[' + id + '][]"]');
                if ($inputs.length === 0) return null;
                if ($inputs.first().is(':disabled')) return null;

                let tagName = $inputs.first().prop('tagName');
                let type = $inputs.first().attr('type');

                if (tagName === 'SELECT') {
                    return $inputs.val();
                } else if (tagName === 'TEXTAREA') {
                    return $inputs.val();
                } else if (type === 'radio') {
                    return $('[name="jawaban[' + id + ']"]:checked').val() || null;
                } else if (type === 'checkbox') {
                    let vals = [];
                    $('[name="jawaban[' + id + '][]"]:checked').each(function() {
                        vals.push($(this).val());
                    });
                    return vals.length > 0 ? vals : null;
                } else {
                    return $inputs.val();
                }
            }

            function checkCondition(syaratId, syaratJawabanJson) {
                if (!syaratId) return true;

                let userVal = getQuestionValue(syaratId);
                if (userVal === undefined || userVal === null || userVal === "") return false;

                let syaratJawaban = [];
                try {
                    if (typeof syaratJawabanJson === 'object') {
                        syaratJawaban = syaratJawabanJson;
                    } else {
                        syaratJawaban = JSON.parse(syaratJawabanJson) || [];
                    }
                } catch (e) {}

                if (!Array.isArray(syaratJawaban)) {
                    syaratJawaban = [syaratJawaban];
                }

                if (Array.isArray(userVal)) {
                    let match = false;
                    for (let i = 0; i < userVal.length; i++) {
                        let val = String(userVal[i]).trim().toLowerCase();
                        if (syaratJawaban.some(sj => String(sj).trim().toLowerCase() === val)) {
                            match = true;
                            break;
                        }
                    }
                    return match;
                } else {
                    let val = String(userVal).trim().toLowerCase();
                    return syaratJawaban.some(sj => String(sj).trim().toLowerCase() === val);
                }
            }

            function evaluateSkipLogic() {
                // Reset state
                $('.kategori-container').removeClass('skip-hidden');
                $('.pertanyaan-container').removeClass('skip-hidden');
                $('form input:not([type="hidden"]), form select, form textarea').prop('disabled', false);

                $('.kategori-container').each(function() {
                    let syaratId = $(this).attr('data-syarat-pertanyaan');
                    let syaratJawabanJson = $(this).attr('data-syarat-jawaban');
                    if (syaratId) {
                        let show = checkCondition(syaratId, syaratJawabanJson);
                        if (!show) {
                            $(this).addClass('skip-hidden');
                            $(this).find('input, select, textarea').prop('disabled', true);
                        }
                    }
                });

                $('.pertanyaan-container').each(function() {
                    let syaratId = $(this).attr('data-syarat-pertanyaan');
                    let syaratJawabanJson = $(this).attr('data-syarat-jawaban');
                    if (syaratId) {
                        let show = checkCondition(syaratId, syaratJawabanJson);
                        if (!show) {
                            $(this).addClass('skip-hidden');
                            $(this).find('input, select, textarea').prop('disabled', true);
                        }
                    }
                });

            }

            // Init skip logic
            evaluateSkipLogic();

            // Run on change
            $('form').on('change input', 'input, select, textarea', function() {
                evaluateSkipLogic();
            });

            $('#btn_submit_tracer').click(function(e) {
                e.preventDefault();

                let form = $(this).closest('form')[0];

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: "Pastikan semua jawaban sudah benar sebelum mengirim kuesioner ini.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: "Ya, Kirim!",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: 'btn btn-secondary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Tunggu Sebentar...',
                            text: 'Sedang menyimpan jawaban Anda.',
                            icon: 'info',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
