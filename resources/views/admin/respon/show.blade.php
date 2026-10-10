@extends('layouts.main')

@section('title', 'Detail Respon Tracer Study')

@section('css')
    <style>
        .header-corak-container {
            position: relative;
            background: linear-gradient(135deg, #f8faff 0%, #ffffff 40%, #f4f8fc 100%);
            overflow: hidden;
        }

        .header-corak-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(rgba(0, 158, 247, 0.09) 1.5px, transparent 1.5px);
            background-size: 20px 20px;
            pointer-events: none;
            opacity: 0.9;
        }

        .copy-nim-btn {
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .copy-nim-btn:hover {
            background-color: #eef3f7 !important;
            border-color: #cbd5e1 !important;
        }

        .header-responsive-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .header-responsive-grid .grid-title {
            order: 1;
        }

        .header-responsive-grid .grid-profile {
            order: 2;
        }

        .header-responsive-grid .grid-stats {
            order: 3;
        }

        @media (min-width: 1200px) {
            .header-responsive-grid {
                display: grid;
                grid-template-columns: 7fr 5fr;
                grid-template-rows: auto auto;
                column-gap: 2rem;
                row-gap: 1.5rem;
            }

            .header-responsive-grid .grid-title {
                grid-column: 1;
                grid-row: 1;
                align-self: end;
                order: unset;
            }

            .header-responsive-grid .grid-stats {
                grid-column: 1;
                grid-row: 2;
                align-self: start;
                order: unset;
            }

            .header-responsive-grid .grid-profile {
                grid-column: 2;
                grid-row: 1 / span 2;
                align-self: center;
                order: unset;
            }
        }

        [data-bs-theme="dark"] .header-corak-container {
            background: var(--bs-card-bg) !important;
        }

        [data-bs-theme="dark"] .header-corak-container::before {
            opacity: 0.05;
        }
    </style>
@endsection

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    <div
                        class="card shadow-sm border border-dashed border-primary rounded mb-7 overflow-hidden position-relative">

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
                                        <i class="fas fa-graduation-cap text-primary me-2 fs-6"></i>Tracer Study
                                    </span>
                                    <span class="text-gray-600 fs-7 fw-bold d-none d-sm-inline">Detail Respon Alumni</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 gap-sm-3 my-1">
                                @if ($respon->status == 'Selesai')
                                    <span class="badge badge-success px-4 py-2 fs-7 fw-bolder shadow-sm">
                                        <i class="fas fa-check-circle text-white me-2"></i>SELESAI
                                    </span>
                                @else
                                    <span class="badge badge-warning px-4 py-2 fs-7 fw-bolder shadow-sm text-white">
                                        <i class="fas fa-clock text-white me-2"></i>{{ strtoupper($respon->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body p-6 p-lg-8 header-corak-container">

                            <div class="position-absolute end-0 bottom-0 pointer-events-none"
                                style="transform: translate(15%, 20%) rotate(-15deg); opacity: 0.04;">
                                <i class="fas fa-graduation-cap text-primary" style="font-size: 18rem;"></i>
                            </div>
                            <div class="position-absolute start-50 top-0 pointer-events-none"
                                style="transform: translate(-50%, -40%); width: 450px; height: 320px; background: radial-gradient(circle, rgba(0, 158, 247, 0.07) 0%, transparent 70%);">
                            </div>

                            <div class="header-responsive-grid position-relative z-index-1">

                                <div class="grid-title">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span
                                            class="badge badge-light-primary px-3 py-2 rounded-pill fw-bold fs-7 shadow-xs border border-primary border-opacity-20">
                                            <i class="fas fa-clipboard-check text-primary me-2"></i>Formulir Kuesioner
                                        </span>
                                        @if ($respon->kuesioner && $respon->kuesioner->tgl_mulai)
                                            <span
                                                class="badge badge-light-info px-3 py-2 rounded-pill fw-bold fs-7 border border-info border-opacity-20">
                                                Periode
                                                {{ \Carbon\Carbon::parse($respon->kuesioner->tgl_mulai)->format('Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    <h1 class="text-gray-900 fw-black mb-0 lh-base"
                                        style="font-size: 1.75rem; letter-spacing: -0.5px;">
                                        {{ $respon->kuesioner->judul }}
                                    </h1>
                                </div>

                                <div class="grid-profile">
                                    <div
                                        class="card border border-gray-300 rounded-3 shadow-sm position-relative overflow-hidden">

                                        <div class="position-absolute top-0 start-0 w-100"
                                            style="height: 3px; background: linear-gradient(90deg, #009ef7, #50cd89);">
                                        </div>

                                        <div class="position-absolute end-0 bottom-0 pointer-events-none me-3 mb-1"
                                            style="opacity: 0.04;">
                                            <i class="fas fa-user-graduate text-primary" style="font-size: 5.5rem;"></i>
                                        </div>

                                        <div class="card-body p-5 position-relative z-index-1">

                                            <div
                                                class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-gray-100">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-user-graduate text-primary fs-6"></i>
                                                    <span
                                                        class="text-gray-500 fw-bolder fs-8 text-uppercase tracking-wider">Informasi
                                                        Responden</span>
                                                </div>
                                                <span class="badge badge-light-success px-2 py-1 rounded-pill fw-bold fs-9">
                                                    <i class="fas fa-shield-alt text-success me-1"></i>Terverifikasi
                                                </span>
                                            </div>

                                            <div class="d-flex align-items-center gap-4 mb-4">

                                                <div class="symbol symbol-55px symbol-circle flex-shrink-0 shadow-sm">
                                                    <div class="symbol-label fw-black bg-primary text-white fs-2">
                                                        {{ strtoupper(substr($respon->mahasiswa->nama ?? 'A', 0, 1)) }}
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                                    <h3 class="text-gray-900 fw-bolder fs-5 mb-1 text-truncate"
                                                        title="{{ $respon->mahasiswa->nama ?? 'Mahasiswa' }}">
                                                        {{ $respon->mahasiswa->nama ?? '-' }}
                                                    </h3>
                                                    <div class="d-flex align-items-center">
                                                        <div class="d-inline-flex align-items-center bg-light border border-gray-200 rounded-pill px-3 py-1 cursor-pointer copy-nim-btn"
                                                            data-nim="{{ $respon->mahasiswa_id }}" data-bs-toggle="tooltip"
                                                            title="Klik untuk menyalin NIM">
                                                            <i class="fas fa-id-badge text-primary me-2 fs-7"></i>
                                                            <span
                                                                class="text-gray-800 fw-bold fs-7 font-monospace me-2">{{ $respon->mahasiswa_id }}</span>
                                                            <i class="fas fa-copy text-muted fs-8"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pt-3 border-top border-gray-100 d-flex flex-column gap-2">
                                                <div class="d-flex align-items-center text-gray-700 fs-7 overflow-hidden">
                                                    <i class="fas fa-university text-primary fs-6 text-center me-2 flex-shrink-0" style="width: 18px;"></i>
                                                    <span class="text-gray-500 flex-shrink-0" style="width: 65px;">Fakultas</span>
                                                    <span class="text-gray-500 me-2 flex-shrink-0">:</span>
                                                    <span class="text-gray-900 fw-semibold text-truncate"
                                                        title="{{ $respon->mahasiswa->prodi->fakultas->nama_fakultas ?? '-' }}">{{ $respon->mahasiswa->prodi->fakultas->nama_fakultas ?? '-' }}</span>
                                                </div>

                                                <div class="d-flex align-items-center text-gray-700 fs-7 overflow-hidden">
                                                    <i class="fas fa-graduation-cap text-warning fs-6 text-center me-2 flex-shrink-0" style="width: 18px;"></i>
                                                    <span class="text-gray-500 flex-shrink-0" style="width: 65px;">Prodi</span>
                                                    <span class="text-gray-500 me-2 flex-shrink-0">:</span>
                                                    <span class="text-gray-900 fw-semibold text-truncate"
                                                        title="{{ $respon->mahasiswa->prodi->nama_prodi ?? '-' }}">{{ $respon->mahasiswa->prodi->nama_prodi ?? '-' }}</span>
                                                </div>

                                                @if (!empty($respon->mahasiswa->email) || !empty($respon->mahasiswa->no_hp))
                                                    <div
                                                        class="d-flex flex-wrap align-items-center gap-3 pt-2 mt-1 border-top border-gray-100 text-gray-500 fs-8">
                                                        @if (!empty($respon->mahasiswa->email))
                                                            <span class="d-flex align-items-center text-truncate"
                                                                title="{{ $respon->mahasiswa->email }}">
                                                                <i
                                                                    class="fas fa-envelope text-gray-400 me-1"></i>{{ $respon->mahasiswa->email }}
                                                            </span>
                                                        @endif
                                                        @if (!empty($respon->mahasiswa->no_hp))
                                                            <span class="d-flex align-items-center">
                                                                <i
                                                                    class="fas fa-phone text-gray-400 me-1"></i>{{ $respon->mahasiswa->no_hp }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="grid-stats">
                                    <div class="row g-3">
                                        <div class="col-12 col-sm-6">
                                            <div
                                                class="d-flex align-items-center bg-body rounded-3 p-4 border border-gray-200 border-start border-start-4 border-start-primary shadow-xs">
                                                <div
                                                    class="symbol symbol-40px bg-light-primary rounded-3 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-calendar-alt text-primary fs-5"></i>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span
                                                        class="text-gray-500 fw-bold fs-8 text-uppercase tracking-wider">Tanggal
                                                        Isi</span>
                                                    <span class="text-gray-800 fw-bolder fs-7">
                                                        {{ \Carbon\Carbon::parse($respon->tgl_isi)->translatedFormat('d F Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <div
                                                class="d-flex align-items-center bg-body rounded-3 p-4 border border-gray-200 border-start border-start-4 border-start-warning shadow-xs">
                                                <div
                                                    class="symbol symbol-40px bg-light-warning rounded-3 me-3 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-clock text-warning fs-5"></i>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span
                                                        class="text-gray-500 fw-bold fs-8 text-uppercase tracking-wider">Waktu</span>
                                                    <span class="text-gray-800 fw-bolder fs-7">
                                                        {{ date('H:i', strtotime($respon->tgl_isi)) }} WIB
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border border-dashed border-dark rounded mb-7">
                        <div
                            class="card-header border-bottom py-5 px-6 px-lg-8 d-flex align-items-center justify-content-between rounded-top">
                            <div class="d-flex align-items-center">
                                <span
                                    class="symbol symbol-40px bg-light-primary rounded-3 me-4 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-list-check text-primary fs-3"></i>
                                </span>
                                <div class="d-flex flex-column">
                                    <h3 class="fw-bolder text-gray-900 m-0 fs-4">Daftar Jawaban Responden</h3>
                                    <span class="text-muted fs-8 fw-semibold mt-1">Rekapitulasi lengkap isian kuesioner
                                        dari alumni yang bersangkutan</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-6 p-lg-10">
                            <form id="form_respon">
                                @foreach ($respon->kuesioner->kategoriPertanyaans as $kategori)
                                    <div class="kategori-container"
                                        data-syarat-pertanyaan="{{ $kategori->syarat_pertanyaan_id }}"
                                        data-syarat-jawaban="{{ json_encode($kategori->syarat_jawaban) }}">
                                        <div class="row">
                                            @foreach ($kategori->pertanyaans as $pertanyaan)
                                                @php
                                                    $isKompetensi = str_contains(
                                                        strtolower($kategori->nama_kategori),
                                                        'kompetensi',
                                                    );
                                                    $colClass = $isKompetensi ? 'col-12 col-md-6' : 'col-12';
                                                @endphp
                                                <div class="{{ $colClass }} mb-6 pertanyaan-container"
                                                    data-syarat-pertanyaan="{{ $pertanyaan->syarat_pertanyaan_id }}"
                                                    data-syarat-jawaban="{{ json_encode($pertanyaan->syarat_jawaban) }}">
                                                    @php
                                                        $answer = $jawabanUser[$pertanyaan->id_pertanyaan] ?? null;
                                                    @endphp
                                                    <label
                                                        class="form-label fs-5 fw-bold text-gray-800 {{ $pertanyaan->wajib ? 'required' : '' }} mb-3">
                                                        {!! $pertanyaan->kode_pertanyaan ? '[' . $pertanyaan->kode_pertanyaan . '] ' : '' !!}
                                                        {{ $pertanyaan->teks_pertanyaan }}
                                                    </label>

                                                    @if (in_array($pertanyaan->tipe_jawaban, ['text', 'number', 'email']))
                                                        <input type="{{ $pertanyaan->tipe_jawaban }}"
                                                            class="form-control border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            value="{{ is_array($answer) ? implode(', ', $answer) : $answer }}"
                                                            disabled>
                                                    @elseif($pertanyaan->tipe_jawaban == 'textarea')
                                                        <textarea class="form-control border-gray-300" name="jawaban[{{ $pertanyaan->id_pertanyaan }}]" rows="4"
                                                            disabled>{{ $answer }}</textarea>
                                                    @elseif($pertanyaan->tipe_jawaban == 'radio')
                                                        <div class="row g-3">
                                                            @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                                <div
                                                                    class="{{ $isKompetensi ? 'col' : 'col-12 col-md-6' }}">
                                                                    <label
                                                                        class="d-flex {{ $isKompetensi ? 'flex-column justify-content-center align-items-center' : 'align-items-center' }} border border-dashed border-gray-300 rounded {{ $isKompetensi ? 'p-3' : 'p-4' }} bg-hover-light cursor-pointer transition-base"
                                                                        style="transition: all 0.2s ease; height: 100%;">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-primary {{ $isKompetensi ? 'mb-2' : 'me-4' }}">
                                                                            <input
                                                                                class="form-check-input {{ $isKompetensi ? 'mx-auto' : '' }}"
                                                                                type="radio"
                                                                                value="{{ $opsi }}"
                                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                                {{ $answer == $opsi ? 'checked' : '' }}
                                                                                disabled>
                                                                        </div>
                                                                        <span
                                                                            class="text-gray-700 fw-semibold fs-6 {{ $isKompetensi ? 'text-center' : 'text-start' }}">
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
                                                                        class="d-flex {{ $isKompetensi ? 'flex-column justify-content-center align-items-center' : 'align-items-center' }} border border-dashed border-gray-300 rounded {{ $isKompetensi ? 'p-3' : 'p-4' }} bg-hover-light cursor-pointer transition-base"
                                                                        style="transition: all 0.2s ease; height: 100%;">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-primary {{ $isKompetensi ? 'mb-2' : 'me-4' }}">
                                                                            <input
                                                                                class="form-check-input check-group-{{ $pertanyaan->id_pertanyaan }} {{ $isKompetensi ? 'mx-auto' : '' }}"
                                                                                type="checkbox"
                                                                                value="{{ $opsi }}"
                                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}][]"
                                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                                {{ is_array($answer) && in_array($opsi, $answer) ? 'checked' : '' }}
                                                                                disabled>
                                                                        </div>
                                                                        <span
                                                                            class="text-gray-700 fw-semibold fs-6 {{ $isKompetensi ? 'text-center' : 'text-start' }}">
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
                                                                <option value="{{ $opsi }}"
                                                                    {{ $answer == $opsi ? 'selected' : '' }}>
                                                                    {{ $opsi }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($pertanyaan->tipe_jawaban == 'date')
                                                        <input type="date" class="form-control border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            value="{{ $answer ? date('Y-m-d', strtotime($answer)) : '' }}"
                                                            disabled>
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
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            function getQuestionValue(id) {
                let $inputs = $('[name="jawaban[' + id + ']"], [name="jawaban[' + id + '][]"]');
                if ($inputs.length === 0) return null;

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
                } catch (e) {}

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

            evaluateSkipLogic();

            $('.copy-nim-btn').on('click', function() {
                let nim = $(this).attr('data-nim');
                if (!nim) return;

                function showSuccessNotification() {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('NIM ' + nim + ' berhasil disalin ke clipboard!');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'NIM ' + nim + ' disalin!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                }

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(nim).then(showSuccessNotification).catch(function() {
                        fallbackCopy(nim);
                    });
                } else {
                    fallbackCopy(nim);
                }

                function fallbackCopy(text) {
                    let $temp = $('<input>');
                    $('body').append($temp);
                    $temp.val(text).select();
                    document.execCommand('copy');
                    $temp.remove();
                    showSuccessNotification();
                }
            });
        });
    </script>
@endsection

