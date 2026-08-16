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
                                        Periode: {{ $periodeAktif->nama_periode }}
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
                                    <div class="mb-10 p-8 rounded shadow-sm border border-dashed border-gray-300 kategori-container" data-syarat-pertanyaan="{{ $kategori->syarat_pertanyaan_id }}" data-syarat-jawaban="{{ json_encode($kategori->syarat_jawaban) }}">
                                        <div class="mb-8 border-bottom border-primary border-2 pb-4 d-flex align-items-center">
                                            <span class="symbol symbol-40px bg-light-primary me-4">
                                                <span class="symbol-label">
                                                    <i class="fas fa-layer-group text-primary fs-3"></i>
                                                </span>
                                            </span>
                                            <h4 class="text-gray-900 fw-bolder m-0 fs-3">{{ $kategori->nama_kategori }}</h4>
                                        </div>

                                        <div class="row">
                                            @foreach ($kategori->pertanyaans as $pertanyaan)
                                                @php
                                                    $isKompetensi = str_contains(strtolower($kategori->nama_kategori), 'kompetensi');
                                                    $colClass = $isKompetensi ? 'col-12 col-md-6' : 'col-12';
                                                @endphp
                                                <div class="{{ $colClass }} mb-6 pertanyaan-container" data-syarat-pertanyaan="{{ $pertanyaan->syarat_pertanyaan_id }}" data-syarat-jawaban="{{ json_encode($pertanyaan->syarat_jawaban) }}">
                                                    @php
                                                        $answer = $jawabanUser[$pertanyaan->id_pertanyaan] ?? null;
                                                    @endphp
                                                    <label class="form-label fs-5 fw-bold text-gray-800 {{ $pertanyaan->wajib ? 'required' : '' }} mb-3">
                                                        {{ $pertanyaan->teks_pertanyaan }}
                                                    </label>

                                                    @if ($pertanyaan->tipe_jawaban == 'text')
                                                        <input type="text" class="form-control border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            placeholder="Ketik jawaban Anda di sini..."
                                                            value="{{ $answer }}"
                                                            {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                    @elseif($pertanyaan->tipe_jawaban == 'textarea')
                                                        <textarea class="form-control border-gray-300" name="jawaban[{{ $pertanyaan->id_pertanyaan }}]" rows="4"
                                                            placeholder="Ketik jawaban Anda di sini..." {{ $pertanyaan->wajib ? 'required' : '' }}>{{ $answer }}</textarea>
                                                    @elseif($pertanyaan->tipe_jawaban == 'radio')
                                                        <div class="row g-3">
                                                            @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                                <div class="{{ $isKompetensi ? 'col' : 'col-12 col-md-6' }}">
                                                                    <label class="d-flex {{ $isKompetensi ? 'flex-column justify-content-center align-items-center' : 'align-items-center' }} border border-dashed border-gray-300 rounded {{ $isKompetensi ? 'p-3' : 'p-4' }} bg-hover-light cursor-pointer transition-base" style="transition: all 0.2s ease; height: 100%;">
                                                                        <div class="form-check form-check-custom form-check-primary {{ $isKompetensi ? 'mb-2' : 'me-4' }}">
                                                                            <input class="form-check-input {{ $isKompetensi ? 'mx-auto' : '' }}" type="radio"
                                                                                value="{{ $opsi }}"
                                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                                {{ $answer == $opsi ? 'checked' : '' }}
                                                                                {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                                        </div>
                                                                        <span class="text-gray-700 fw-semibold fs-6 {{ $isKompetensi ? 'text-center' : 'text-start' }}">
                                                                            {{ $opsi }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($pertanyaan->tipe_jawaban == 'checkbox')
                                                        <div class="row g-3">
                                                            @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                                <div class="{{ $isKompetensi ? 'col' : 'col-12 col-md-6' }}">
                                                                    <label class="d-flex {{ $isKompetensi ? 'flex-column justify-content-center align-items-center' : 'align-items-center' }} border border-dashed border-gray-300 rounded {{ $isKompetensi ? 'p-3' : 'p-4' }} bg-hover-light cursor-pointer transition-base" style="transition: all 0.2s ease; height: 100%;">
                                                                        <div class="form-check form-check-custom form-check-primary {{ $isKompetensi ? 'mb-2' : 'me-4' }}">
                                                                            <input
                                                                                class="form-check-input check-group-{{ $pertanyaan->id_pertanyaan }} {{ $isKompetensi ? 'mx-auto' : '' }}"
                                                                                type="checkbox" value="{{ $opsi }}"
                                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}][]"
                                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                                {{ is_array($answer) && in_array($opsi, $answer) ? 'checked' : '' }}>
                                                                        </div>
                                                                        <span class="text-gray-700 fw-semibold fs-6 {{ $isKompetensi ? 'text-center' : 'text-start' }}">
                                                                            {{ $opsi }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($pertanyaan->tipe_jawaban == 'select')
                                                        <select class="form-select border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            data-control="select2" data-placeholder="Pilih Jawaban"
                                                            {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                            <option value=""></option>
                                                            @foreach ($pertanyaan->opsi_jawaban as $opsi)
                                                                <option value="{{ $opsi }}" {{ $answer == $opsi ? 'selected' : '' }}>{{ $opsi }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($pertanyaan->tipe_jawaban == 'date')
                                                        <input type="date" class="form-control border-gray-300 w-250px"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            value="{{ $answer ? date('Y-m-d', strtotime($answer)) : '' }}"
                                                            {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <div class="separator my-10"></div>
                                <div class="d-flex justify-content-end mb-5">
                                    <button type="button" id="btn_submit_tracer" class="btn btn-primary btn-lg px-8">
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
    <script>
        $(document).ready(function() {
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
                    syaratJawaban = JSON.parse(syaratJawabanJson) || [];
                } catch(e) {}

                if (Array.isArray(userVal)) {
                    let match = false;
                    for (let i = 0; i < userVal.length; i++) {
                        if (syaratJawaban.includes(userVal[i])) {
                            match = true;
                            break;
                        }
                    }
                    return match;
                } else {
                    return syaratJawaban.includes(userVal);
                }
            }

            function evaluateSkipLogic() {
                // Reset state
                $('.kategori-container').show();
                $('.pertanyaan-container').show();
                $('form input:not([type="hidden"]), form select, form textarea').prop('disabled', false);

                $('.kategori-container').each(function() {
                    let syaratId = $(this).attr('data-syarat-pertanyaan');
                    let syaratJawabanJson = $(this).attr('data-syarat-jawaban');
                    if (syaratId) {
                        let show = checkCondition(syaratId, syaratJawabanJson);
                        if (!show) {
                            $(this).hide();
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
                            $(this).hide();
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
