@extends('layouts.main')

@section('title', 'Detail Respon Tracer Study')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    <div class="mb-5 d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.respon.index') }}" class="btn btn-sm btn-light-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal_json_kemdikbud">
                            <i class="fas fa-code me-2"></i>Lihat JSON Kemdikbud
                        </button>
                    </div>

                    <div class="card shadow-sm border border-dashed border-primary rounded mb-5">
                        <div class="card-header pt-8 pb-4 border-0 d-flex align-items-center">
                            <div class="d-flex align-items-center w-100">
                                <!-- Icon Badge -->
                                <div class="symbol symbol-60px symbol-circle shadow-sm me-5">
                                    <div class="symbol-label bg-light-primary d-flex align-items-center justify-content-center">
                                        <i class="fas fa-clipboard-check text-primary fs-2hx"></i>
                                    </div>
                                </div>
                                
                                <!-- Title & Info -->
                                <div class="d-flex flex-column flex-grow-1">
                                    <h2 class="text-gray-900 fw-bolder fs-2 mb-3">{{ $respon->kuesioner->judul }}</h2>
                                    
                                    <div class="d-flex flex-wrap align-items-center gap-4">
                                        <!-- Info: Mahasiswa -->
                                        <div class="d-flex align-items-center bg-light rounded px-4 py-2 shadow-sm">
                                            <i class="fas fa-user text-primary fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6">{{ $respon->mahasiswa->nama }}</span>
                                                <span class="text-muted fw-semibold fs-8">{{ $respon->mahasiswa_id }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Info: Tanggal Isi -->
                                        <div class="d-flex align-items-center bg-light rounded px-4 py-2 shadow-sm">
                                            <i class="fas fa-clock text-success fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6">{{ \Carbon\Carbon::parse($respon->tgl_isi)->translatedFormat('d F Y') }}</span>
                                                <span class="text-muted fw-semibold fs-8">{{ date('H:i', strtotime($respon->tgl_isi)) }} WIB</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="separator my-2"></div>
                        <div class="card-body">
                            <form id="form_respon">
                                @foreach ($respon->kuesioner->kategoriPertanyaans as $kategori)
                                    <div class="kategori-container" data-syarat-pertanyaan="{{ $kategori->syarat_pertanyaan_id }}" data-syarat-jawaban="{{ json_encode($kategori->syarat_jawaban) }}">
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
                                                        {!! $pertanyaan->kode_pertanyaan ? '[' . $pertanyaan->kode_pertanyaan . '] ' : '' !!}
                                                        {{ $pertanyaan->teks_pertanyaan }}
                                                    </label>

                                                    @if (in_array($pertanyaan->tipe_jawaban, ['text', 'number', 'email']))
                                                        <input type="{{ $pertanyaan->tipe_jawaban }}" class="form-control border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            value="{{ is_array($answer) ? implode(', ', $answer) : $answer }}" disabled>
                                                    @elseif($pertanyaan->tipe_jawaban == 'textarea')
                                                        <textarea class="form-control border-gray-300" name="jawaban[{{ $pertanyaan->id_pertanyaan }}]" rows="4" disabled>{{ $answer }}</textarea>
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
                                                                                {{ $answer == $opsi ? 'checked' : '' }} disabled>
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
                                                                                {{ is_array($answer) && in_array($opsi, $answer) ? 'checked' : '' }} disabled>
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
                                                            data-control="select2" disabled>
                                                            <option value=""></option>
                                                            @foreach ($pertanyaan->opsi_jawaban as $opsi)
                                                                <option value="{{ $opsi }}" {{ $answer == $opsi ? 'selected' : '' }}>{{ $opsi }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($pertanyaan->tipe_jawaban == 'date')
                                                        <input type="date" class="form-control border-gray-300 w-250px"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            value="{{ $answer ? date('Y-m-d', strtotime($answer)) : '' }}" disabled>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>

    <!-- Modal JSON Kemdikbud -->
    <div class="modal fade" id="modal_json_kemdikbud" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Format JSON Kemdikbud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-center p-5 mb-5">
                        <i class="fas fa-info-circle fs-2hx text-info me-4"></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-info">Informasi</h4>
                            <span>JSON ini dihasilkan berdasarkan "Kode Pertanyaan" yang diatur di master kuesioner. Pertanyaan yang tidak memiliki kode akan diabaikan.</span>
                        </div>
                    </div>
                    <div class="bg-dark rounded p-5">
                        <pre class="text-white mb-0" style="white-space: pre-wrap;"><code>{{ json_encode($jsonKemdikbud, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="navigator.clipboard.writeText(`{{ json_encode($jsonKemdikbud) }}`).then(() => Swal.fire('Berhasil!', 'JSON berhasil disalin ke clipboard', 'success'))">
                        <i class="fas fa-copy me-2"></i>Salin JSON
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            function getQuestionValue(id) {
                let $inputs = $('[name="jawaban[' + id + ']"], [name="jawaban[' + id + '][]"]');
                if ($inputs.length === 0) return null;
                // Kita tidak mengecek is(':disabled') karena semua input di halaman ini di-disable

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
                $('.kategori-container').show();
                $('.pertanyaan-container').show();

                $('.kategori-container').each(function() {
                    let syaratId = $(this).attr('data-syarat-pertanyaan');
                    let syaratJawabanJson = $(this).attr('data-syarat-jawaban');
                    if (syaratId) {
                        let show = checkCondition(syaratId, syaratJawabanJson);
                        if (!show) {
                            $(this).hide();
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
                        }
                    }
                });
            }

            // Init skip logic
            evaluateSkipLogic();
        });
    </script>
@endsection
