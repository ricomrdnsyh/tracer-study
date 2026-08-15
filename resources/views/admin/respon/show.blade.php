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
                        <div class="card-header pt-6 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-clipboard-list text-primary fs-2 me-3"></i>
                                    <span class="card-label fw-bolder fs-2">{{ $respon->kuesioner->judul }}</span>
                                </div>
                                <span class="text-muted mt-2 fw-bold fs-7">
                                    <i class="fas fa-user me-1 text-muted"></i> Mahasiswa:
                                    {{ $respon->mahasiswa->nama }} ({{ $respon->mahasiswa_id }})
                                </span>
                                <span class="text-muted mt-1 fw-bold fs-7">
                                    <i class="fas fa-clock me-1 text-muted"></i> Waktu Isi:
                                    {{ date('d M Y H:i', strtotime($respon->tgl_isi)) }}
                                </span>
                            </h3>
                        </div>
                        <div class="separator my-2"></div>
                        <div class="card-body">
                            <form id="form_respon">
                                @foreach ($respon->kuesioner->kategoriPertanyaans as $kategori)
                                    <div class="mb-10 p-5 rounded border border-dashed border-gray-300 kategori-container" data-syarat-pertanyaan="{{ $kategori->syarat_pertanyaan_id }}" data-syarat-jawaban="{{ json_encode($kategori->syarat_jawaban) }}">
                                        <h4 class="mb-7 text-dark fw-bolder bg-light-primary px-4 py-3 rounded d-flex align-items-center">
                                            <i class="fas fa-layer-group text-primary me-3"></i>{{ $kategori->nama_kategori }}
                                        </h4>

                                        @foreach ($kategori->pertanyaans as $pertanyaan)
                                            <div class="mb-8 px-4 pertanyaan-container" data-syarat-pertanyaan="{{ $pertanyaan->syarat_pertanyaan_id }}" data-syarat-jawaban="{{ json_encode($pertanyaan->syarat_jawaban) }}">
                                                @php
                                                    $answer = $jawabanUser[$pertanyaan->id_pertanyaan] ?? null;
                                                @endphp
                                                <label class="form-label fs-5 fw-bold text-gray-800 {{ $pertanyaan->wajib ? 'required' : '' }} mb-4">
                                                    {{ $pertanyaan->teks_pertanyaan }}
                                                </label>

                                                @if ($pertanyaan->tipe_jawaban == 'text')
                                                    <input type="text" class="form-control form-control-solid"
                                                        name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                        value="{{ $answer }}" disabled>
                                                @elseif($pertanyaan->tipe_jawaban == 'textarea')
                                                    <textarea class="form-control form-control-solid" name="jawaban[{{ $pertanyaan->id_pertanyaan }}]" rows="4" disabled>{{ $answer }}</textarea>
                                                @elseif($pertanyaan->tipe_jawaban == 'radio')
                                                    @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                        <div class="form-check form-check-custom form-check-solid mb-2">
                                                            <input class="form-check-input" type="radio"
                                                                value="{{ $opsi }}"
                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                {{ $answer == $opsi ? 'checked' : '' }} disabled>
                                                            <label class="form-check-label"
                                                                for="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}">
                                                                {{ $opsi }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @elseif($pertanyaan->tipe_jawaban == 'checkbox')
                                                    @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                        <div class="form-check form-check-custom form-check-solid mb-2">
                                                            <input
                                                                class="form-check-input check-group-{{ $pertanyaan->id_pertanyaan }}"
                                                                type="checkbox" value="{{ $opsi }}"
                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}][]"
                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                {{ is_array($answer) && in_array($opsi, $answer) ? 'checked' : '' }} disabled>
                                                            <label class="form-check-label"
                                                                for="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}">
                                                                {{ $opsi }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @elseif($pertanyaan->tipe_jawaban == 'select')
                                                    <select class="form-select form-select-solid"
                                                        name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                        data-control="select2" disabled>
                                                        <option value=""></option>
                                                        @foreach ($pertanyaan->opsi_jawaban as $opsi)
                                                            <option value="{{ $opsi }}" {{ $answer == $opsi ? 'selected' : '' }}>{{ $opsi }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @elseif($pertanyaan->tipe_jawaban == 'date')
                                                    <input type="date" class="form-control form-control-solid w-250px"
                                                        name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                        value="{{ $answer ? date('Y-m-d', strtotime($answer)) : '' }}" disabled>
                                                @endif
                                            </div>
                                        @endforeach
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
