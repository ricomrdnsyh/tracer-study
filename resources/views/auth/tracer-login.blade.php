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

        /* SweetAlert customization */
        .swal2-popup {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body id="kt_body" class="bg-body bg-grid">
    <div class="d-flex flex-column flex-root auth-wrapper">
        <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
            <a href="{{ url('/') }}" class="mb-8">
                <img alt="Logo" src="{{ asset('assets/media/logos/unuja.png') }}" class="h-60px" />
            </a>
            <div class="w-lg-500px mx-auto auth-card">
                <div class="auth-card-inner">
                    <form id="login_form" class="form w-100" action="{{ route('tracer.login.post') }}" method="POST">
                        @csrf
                        <div class="text-center mb-8">
                            <h1 class="text-dark mb-2 auth-title fs-2">Tracer Study Mahasiswa</h1>
                            <p class="text-muted auth-subtitle">Masukkan NIM Anda untuk mengisi kuesioner Tracer Study</p>
                        </div>

                        <div class="fv-row mb-5">
                            <label class="form-label">Nomor Induk Mahasiswa (NIM)</label>
                            <input class="form-control" type="text" name="nim" value="{{ old('nim') }}"
                                placeholder="Masukkan NIM Anda" required autofocus autocomplete="off" />
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-sm btn-lg btn-login w-100 mb-3">
                                Lanjut Isi Kuesioner
                            </button>
                            <div class="text-muted auth-footer-text">
                                Tracer Study Universitas Nurul Jadid
                            </div>
                        </div>
                    </form>
                </div>
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
    </script>

    @if ($errors->any())
        <script>
            Swal.fire({
                text: "{{ $errors->first() }}",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "OK, mengerti",
                customClass: {
                    confirmButton: "btn btn-danger"
                }
            });
        </script>
    @endif

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ?
                '<i class="fas fa-eye"></i>' :
                '<i class="fas fa-eye-slash"></i>';
        });
    </script>
</body>

</html>
