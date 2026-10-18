<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tracer Study UNUJA</title>
    <meta name="description" content="Tracer Study Universitas Nurul Jadid" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/unuja.png') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --border-color: #94a3b8;
        }

        body.bg-grid {
            background-color: #f1f4f8;
            background-image:
                linear-gradient(to right, rgba(206, 206, 206, 0.31) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(206, 206, 206, 0.31) 1px, transparent 1px);
            background-size: 25px 25px;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }

        .auth-wrapper {
            min-height: 100vh;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 18px;
            padding: 2.5rem 2.25rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(148, 163, 184, 0.5);
            position: relative;
            overflow: hidden;
        }

        .auth-card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(59, 130, 246, 0.02));
            opacity: 0.9;
            pointer-events: none;
        }

        .auth-card-inner {
            position: relative;
            z-index: 1;
        }

        .auth-title {
            font-weight: 600;
            letter-spacing: 0.02em;
            color: var(--dark-color);
        }

        .auth-subtitle {
            font-size: 0.95rem;
            color: #6b7280;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 0.75rem;
            border: 1.5px solid #e5e7eb;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group-text {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border: 1.5px solid #e5e7eb;
            background-color: #f9fafb;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.3s ease;
        }

        .input-group-text:hover {
            color: var(--primary-color);
            background-color: #f3f4f6;
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.75rem;
            color: white;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }

        .auth-footer-text {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.5rem 1.15rem;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1.5px solid rgba(203, 213, 225, 0.8);
            border-radius: 50px;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-back-home:hover {
            color: var(--primary-color);
            background: #ffffff;
            border-color: rgba(37, 99, 235, 0.4);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.12);
            transform: translateX(-2px);
        }

        .btn-back-home i {
            font-size: 0.8rem;
            transition: transform 0.25s ease;
        }

        .btn-back-home:hover i {
            transform: translateX(-3px);
        }

        .btn-switch-auth {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.15rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1.5px solid rgba(226, 232, 240, 0.8);
            border-radius: 50px;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .btn-switch-auth:hover {
            color: var(--primary-color);
            background: #ffffff;
            border-color: rgba(37, 99, 235, 0.35);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);
        }

        .btn-switch-auth i {
            font-size: 0.75rem;
            transition: transform 0.25s ease;
        }

        .btn-switch-auth:hover i {
            transform: translateX(3px);
        }

        .btn-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            padding: 0.45rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-back-link:hover {
            color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.06);
            transform: translateX(-2px);
        }

        .btn-back-link i {
            font-size: 0.78rem;
            transition: transform 0.2s ease;
        }

        .btn-back-link:hover i {
            transform: translateX(-3px);
        }

        .link-back {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .link-back:hover {
            color: var(--primary-hover);
        }

        .fv-row {
            margin-bottom: 1.25rem;
        }

        @media (max-width: 576px) {
            .auth-card {
                padding: 2rem 1.5rem;
            }

            .auth-title {
                font-size: 1.5rem;
            }
        }

        .swal2-popup {
            font-family: 'Poppins', sans-serif;
        }

        .select2-container--bootstrap5 .select2-selection--single,
        .select2-container .select2-selection--single {
            height: calc(1.5em + 1.5rem + 2px) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.75rem !important;
            border: 1.5px solid #e5e7eb !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            transition: all 0.3s ease !important;
        }

        .select2-container--bootstrap5.select2-container--focus .select2-selection--single,
        .select2-container--bootstrap5.select2-container--open .select2-selection--single,
        .select2-container--open .select2-selection--single {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            color: var(--dark-color) !important;
            font-size: 0.95rem !important;
            padding-left: 0 !important;
            padding-right: 1.5rem !important;
            font-family: 'Poppins', sans-serif !important;
        }

        .select2-container .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af !important;
            font-weight: 400 !important;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 12px !important;
            top: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-dropdown {
            border-radius: 0.75rem !important;
            border: 1px solid rgba(148, 163, 184, 0.4) !important;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15) !important;
            font-family: 'Poppins', sans-serif !important;
            z-index: 1060 !important;
            overflow: hidden !important;
        }

        .select2-search--dropdown {
            padding: 0.6rem !important;
        }

        .select2-search--dropdown .select2-search__field {
            border-radius: 0.5rem !important;
            border: 1.5px solid #e5e7eb !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            font-family: 'Poppins', sans-serif !important;
        }

        .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary-color) !important;
            outline: none !important;
        }

        .select2-results__option {
            padding: 0.6rem 1rem !important;
            font-size: 0.9rem !important;
            transition: all 0.2s ease !important;
        }

        .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color) !important;
            color: #ffffff !important;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body id="kt_body" class="bg-body bg-grid">
    <div class="d-flex flex-column flex-root auth-wrapper">
        <!-- Top Navigation Bar -->
        <div class="w-100 py-4 px-4 px-md-7 d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="btn-back-home" title="Kembali ke Beranda Tracer Study">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Beranda</span>
            </a>
            <a href="{{ route('login') }}" class="btn-switch-auth d-none d-sm-inline-flex" title="Khusus Dosen, Kaprodi, Fakultas & Admin">
                <i class="fas fa-user-shield me-1"></i>
                <span>Login Pegawai / Admin</span>
                <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="d-flex flex-center flex-column flex-column-fluid px-4 py-5 pb-lg-20">
            <div class="w-lg-500px mx-auto auth-card">
                <div class="auth-card-inner">
                    <div class="text-center mb-7">
                        <a href="{{ route('home') }}" class="d-inline-block mb-4" title="Kembali ke Beranda Tracer Study">
                            <img alt="Logo Tracer Study" src="{{ asset('assets/media/logos/tracer-dark.png') }}"
                                class="h-50px" style="max-height: 50px; width: auto;" />
                        </a>
                        <h1 class="text-dark mb-2 auth-title fs-2">Tracer Study Mahasiswa</h1>
                        <p class="text-muted auth-subtitle">Masukkan NIM Anda untuk mengisi kuesioner Tracer Study</p>
                    </div>

                    @php
                        $fakultas =
                            $fakultas ??
                            \App\Models\Fakultas::with([
                                'prodis' => function ($q) {
                                    $q->orderBy('nama_prodi');
                                },
                            ])
                                ->orderBy('nama_fakultas')
                                ->get();
                    @endphp

                    <form id="login_form" class="form w-100" action="{{ route('tracer.login.post') }}" method="POST">
                        @csrf

                        <div class="fv-row mb-3">
                            <label class="form-label">Nomor Induk Mahasiswa (NIM)</label>
                            <input class="form-control" type="text" name="nim" value="{{ old('nim') }}"
                                placeholder="Masukkan NIM Anda" required autofocus autocomplete="off" />
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="text-muted" style="font-size: 0.825rem;">Lupa NIM Anda?</span>
                            <button type="button" class="btn btn-link p-0 text-decoration-none fw-semibold"
                                style="font-size: 0.825rem; color: var(--primary-color);" data-bs-toggle="modal"
                                data-bs-target="#modalBantuanNim">
                                <i class="fas fa-question-circle me-1"></i> Bantuan Lupa NIM
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-login w-100 mb-4">
                                Lanjut Isi Kuesioner
                            </button>

                            <div class="d-flex align-items-center justify-content-center mb-4">
                                <a href="{{ route('home') }}" class="btn-back-link">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Kembali ke Beranda</span>
                                </a>
                            </div>

                            <div class="text-muted auth-footer-text">
                                Tracer Study Universitas Nurul Jadid
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBantuanNim" tabindex="-1" aria-labelledby="modalBantuanNimLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
                <div class="modal-header border-0 py-4 px-5"
                    style="background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                    <div class="d-flex align-items-center">
                        <div class="me-3 p-3 rounded-circle text-white shadow-sm d-flex align-items-center justify-content-center"
                            style="width: 44px; height: 44px; background: var(--primary-color);">
                            <i class="fas fa-id-card fs-5 text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0 fs-5" id="modalBantuanNimLabel">
                                Bantuan Verifikasi Lupa NIM
                            </h5>
                            <span class="text-muted" style="font-size: 0.8rem;">Pihak LPIP UNUJA akan memverifikasi dan
                                menghubungi Anda via WhatsApp</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="form_bantuan_nim" action="{{ route('tracer.bantuan_nim.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 p-md-5">
                        <div class="p-3 mb-4 rounded-3 d-flex align-items-start"
                            style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                            <i class="fab fa-whatsapp text-success fs-4 me-3 mt-1"></i>
                            <div style="font-size: 0.85rem; color: #166534;">
                                <strong>Layanan Bantuan Alumni:</strong> Silakan lengkapi identitas Anda di bawah ini.
                                Setelah formulir dikirim, notifikasi akan langsung masuk ke email LPIP dan staf kami
                                akan segera menghubungi nomor WhatsApp aktif Anda.
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="bantuan_nama" class="form-control"
                                    placeholder="Contoh: Ahmad Rico" required />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label mb-1">Fakultas <span class="text-danger">*</span></label>
                                <select name="fakultas_id" id="bantuan_fakultas_id"
                                    class="form-select form-select-solid" data-control="select2"
                                    data-dropdown-parent="#modalBantuanNim" data-placeholder="Pilih Fakultas"
                                    data-allow-clear="true" style="width: 100%;" required>
                                    <option value=""></option>
                                    @foreach ($fakultas as $fak)
                                        <option value="{{ $fak->id_fakultas }}">{{ $fak->nama_fakultas }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label mb-1">Program Studi <span
                                        class="text-danger">*</span></label>
                                <select name="prodi_id" id="bantuan_prodi_id" class="form-select form-select-solid"
                                    data-control="select2" data-dropdown-parent="#modalBantuanNim"
                                    data-placeholder="Pilih Program Studi" data-allow-clear="true"
                                    style="width: 100%;" required disabled>
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label mb-1">Nomor WhatsApp Aktif <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i
                                            class="fab fa-whatsapp text-success me-1"></i></span>
                                    <input type="number" name="no_wa" id="bantuan_no_wa" class="form-control"
                                        placeholder="Contoh: 081234567890" required />
                                </div>
                                <span class="text-muted" style="font-size: 0.75rem;">Pastikan nomor terhubung dengan
                                    WhatsApp aktif</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label mb-1">Perkiraan Tahun Kelulusan <span
                                        class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="number" name="tahun_lulus" id="bantuan_tahun_lulus"
                                    class="form-control" placeholder="Contoh: 2023" min="1990"
                                    max="{{ date('Y') + 1 }}" />
                            </div>

                            <div class="col-12">
                                <label class="form-label mb-1">Catatan Tambahan <span
                                        class="text-muted fw-normal">(Opsional)</span></label>
                                <textarea name="keterangan" id="bantuan_keterangan" class="form-control" rows="2"
                                    placeholder="Catatan tambahan jika ada"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 py-3 px-5" style="background-color: #f8fafc;">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btn_submit_bantuan" class="btn btn-login px-5">
                            <i class="fas fa-paper-plane me-1"></i> Kirim Permohonan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const form = document.getElementById('login_form');
        form.addEventListener('submit', function(event) {
            Swal.fire({
                icon: 'info',
                title: 'Mohon tunggu...',
                text: 'Permintaan anda sedang diproses',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        const prodisByFakultas = @json(
            $fakultas->mapWithKeys(function ($f) {
                return [
                    $f->id_fakultas => $f->prodis->map(function ($p) {
                        return ['id_prodi' => $p->id_prodi, 'nama_prodi' => $p->nama_prodi];
                    }),
                ];
            }));

        $(document).ready(function() {
            const $modal = $('#modalBantuanNim');
            const $fakultas = $('#bantuan_fakultas_id');
            const $prodi = $('#bantuan_prodi_id');

            function initModalSelect2() {
                $fakultas.select2({
                    dropdownParent: $modal,
                    placeholder: 'Pilih Fakultas',
                    allowClear: true,
                    width: '100%'
                });

                $prodi.select2({
                    dropdownParent: $modal,
                    placeholder: 'Pilih Program Studi',
                    allowClear: true,
                    width: '100%'
                });
            }

            initModalSelect2();

            $modal.on('shown.bs.modal', function() {
                initModalSelect2();
            });

            $fakultas.on('change', function() {
                const fakultasId = $(this).val();
                $prodi.empty().append('<option value=""></option>');

                if (fakultasId && prodisByFakultas[fakultasId]) {
                    prodisByFakultas[fakultasId].forEach(function(item) {
                        const opt = new Option(item.nama_prodi, item.id_prodi, false, false);
                        $prodi.append(opt);
                    });
                    $prodi.prop('disabled', false);
                } else {
                    $prodi.prop('disabled', true);
                }

                $prodi.val(null).trigger('change');
            });

            $modal.on('hidden.bs.modal', function() {
                const formEl = document.getElementById('form_bantuan_nim');
                if (formEl) formEl.reset();
                $fakultas.val(null).trigger('change');
                $prodi.empty().append('<option value=""></option>').val(null).prop('disabled', true)
                    .trigger('change');
            });

            const formBantuan = document.getElementById('form_bantuan_nim');
            if (formBantuan) {
                formBantuan.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btnSubmit = document.getElementById('btn_submit_bantuan');
                    const originalHtml = btnSubmit.innerHTML;
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Mengirim...';

                    const formData = new FormData(formBantuan);

                    fetch(formBantuan.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errData => {
                                    throw errData;
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const modalEl = document.getElementById('modalBantuanNim');
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                            formBantuan.reset();
                            $fakultas.val(null).trigger('change');
                            $prodi.empty().append('<option value=""></option>').val(null).prop(
                                'disabled', true).trigger('change');

                            Swal.fire({
                                title: 'Permohonan Berhasil Dikirim!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'Tutup',
                                customClass: {
                                    confirmButton: 'btn btn-login'
                                }
                            });
                        })
                        .catch(err => {
                            let errorMsg = 'Terjadi kendala saat mengirim permohonan.';
                            if (err && err.errors) {
                                errorMsg = Object.values(err.errors).flat().join('<br>');
                            } else if (err && err.message) {
                                errorMsg = err.message;
                            }
                            Swal.fire({
                                title: 'Gagal Mengirim',
                                html: errorMsg,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                }
                            });
                        })
                        .finally(() => {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = originalHtml;
                        });
                });
            }
        });
    </script>

    @if (session('error'))
        <script>
            Swal.fire({
                text: "{{ session('error') }}",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "OK, mengerti",
                customClass: {
                    confirmButton: "btn btn-danger"
                }
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                text: "{{ session('success') }}",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "OK",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                title: "NIM Tidak Ditemukan",
                text: "{{ $errors->first() }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "<i class='fas fa-question-circle me-1'></i> Bantuan Lupa NIM",
                cancelButtonText: "Coba Lagi",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-login me-3",
                    cancelButton: "btn btn-secondary"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const modalEl = document.getElementById('modalBantuanNim');
                    if (modalEl) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }
            });
        </script>
    @endif
</body>

</html>
