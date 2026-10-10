@extends('layouts.main')

@section('title', 'Dashboard')

@section('css')
    <style>
        :root {
            --primary-soft: #f0f7ff;
            --primary-color: #006ae6;
            --success-soft: #ecfdf3;
            --success-color: #12b76a;
            --warning-soft: #fffcf5;
            --warning-color: #f79009;
            --info-soft: #f0f9ff;
            --info-color: #0ea5e9;
            --border-color: #eaecf0;
            --text-main: #101828;
            --text-muted: #667085;
        }

        [data-bs-theme="dark"] {
            --primary-soft: rgba(0, 106, 230, 0.15);
            --success-soft: rgba(18, 183, 106, 0.15);
            --warning-soft: rgba(247, 144, 9, 0.15);
            --info-soft: rgba(14, 165, 233, 0.15);
            --border-color: var(--bs-border-color);
            --text-main: var(--bs-text-primary);
            --text-muted: var(--bs-text-muted);
        }

        [data-bs-theme="dark"] .glass-card {
            background: var(--bs-card-bg);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        [data-bs-theme="dark"] .status-done {
            background: linear-gradient(145deg, var(--bs-card-bg), rgba(18, 183, 106, 0.05));
            border-color: rgba(18, 183, 106, 0.2);
        }

        [data-bs-theme="dark"] .status-pending {
            background: linear-gradient(145deg, var(--bs-card-bg), rgba(247, 144, 9, 0.05));
            border-color: rgba(247, 144, 9, 0.2);
        }

        [data-bs-theme="dark"] .step-icon-container {
            background: var(--bs-app-bg-color);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        [data-bs-theme="dark"] .step-num-badge {
            border-color: var(--bs-card-bg);
            color: #ffffff;
        }

        /* HERO SECTION */
        .dash-hero {
            background: linear-gradient(135deg, #006AE6 0%, #004CCC 100%);
            border-radius: 28px;
            padding: 3.5rem 4rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 106, 230, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dash-hero::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .dash-hero-pattern {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.5;
        }

        .hero-illus {
            position: absolute;
            right: 2rem;
            bottom: -2rem;
            height: 110%;
            object-fit: contain;
            opacity: 0.95;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.1));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .avatar-initial {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        /* CARDS */
        .glass-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(16, 24, 40, 0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(16, 24, 40, 0.08);
            border-color: #d0d5dd;
        }

        /* INFO CHIPS */
        .info-chip {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            gap: 1.25rem;
            height: 100%;
        }

        .icon-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .glass-card:hover .icon-box {
            transform: scale(1.05);
        }

        /* STATUS BANNER */
        .status-banner {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
        }

        .status-banner::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 6px;
        }

        .status-done {
            background: linear-gradient(145deg, #ffffff, #f6fef9);
            border: 1px solid #d1fadf;
        }

        .status-done::before {
            background: var(--success-color);
        }

        .status-done .status-icon {
            color: var(--success-color);
            background: var(--success-soft);
        }

        .status-pending {
            background: linear-gradient(145deg, #ffffff, #fffdf5);
            border: 1px solid #fef0c7;
        }

        .status-pending::before {
            background: var(--warning-color);
        }

        .status-pending .status-icon {
            color: var(--warning-color);
            background: var(--warning-soft);
        }

        .status-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.04);
        }

        @media (min-width: 768px) {
            .status-icon {
                width: 80px;
                height: 80px;
                border-radius: 20px;
                font-size: 2.5rem;
            }
        }

        /* PROGRESS BAR */
        .progress-track {
            background: var(--border-color);
            height: 8px;
            border-radius: 10px;
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes pulse-arrow {
            0% {
                transform: translateX(0);
                opacity: 1;
            }

            50% {
                transform: translateX(6px);
                opacity: 0.6;
            }

            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* STEPS */
        .step-wrapper {
            position: relative;
            padding: 2.5rem 2rem;
            text-align: center;
            z-index: 1;
        }

        .step-icon-container {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.75rem;
            position: relative;
            background: white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            z-index: 2;
        }

        .step-num-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--text-main);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        /* QUICK LINKS */
        .quick-link {
            padding: 1.75rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            text-decoration: none !important;
            color: inherit;
        }

        .quick-arrow {
            margin-left: auto;
            color: var(--border-color);
            transition: all 0.3s ease;
            font-size: 1.25rem;
        }

        .glass-card:hover .quick-arrow {
            color: var(--primary-color);
            transform: translateX(4px);
        }

        /* TYPOGRAPHY HELPER */
        .text-main {
            color: var(--text-main);
        }

        .text-muted {
            color: var(--text-muted);
        }

        @media (min-width: 768px) {
            .border-md-end {
                border-right: 1px solid var(--border-color);
            }
        }

        @media (max-width: 991px) {
            .dash-hero {
                padding: 2.5rem 1.5rem;
            }

            .hero-illus {
                display: none;
            }

            .status-banner {
                text-align: center;
            }

            .progress-track {
                margin: 0 auto;
            }
        }
    </style>
@endsection

@section('content')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid mt-8 mb-10">
            <div id="kt_app_content_container" class="app-container container-fluid">

                @php
                    $jam = (int) date('G');
                    $sapaan =
                        $jam < 11
                            ? 'Selamat Pagi'
                            : ($jam < 15
                                ? 'Selamat Siang'
                                : ($jam < 18
                                    ? 'Selamat Sore'
                                    : 'Selamat Malam'));
                @endphp

                
                <div class="dash-hero" style="margin-bottom: -3rem; padding-bottom: 5rem;">
                    <div class="dash-hero-pattern"></div>

                    <svg class="hero-illus d-none d-lg-block" width="380" height="380" viewBox="0 0 380 380"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        
                        <circle cx="190" cy="190" r="140" fill="white" fill-opacity="0.05" />
                        <circle cx="190" cy="190" r="100" fill="white" fill-opacity="0.1" />

                        
                        <g transform="translate(50, 70) rotate(-12)">
                            <rect x="0" y="0" width="130" height="170" rx="16" fill="white"
                                fill-opacity="0.95" style="filter: drop-shadow(0 12px 24px rgba(0,0,0,0.15));" />
                            <rect x="24" y="32" width="50" height="12" rx="6" fill="#006AE6"
                                fill-opacity="0.15" />
                            <rect x="24" y="60" width="82" height="8" rx="4" fill="#006AE6"
                                fill-opacity="0.1" />
                            <rect x="24" y="80" width="60" height="8" rx="4" fill="#006AE6"
                                fill-opacity="0.1" />
                            <rect x="24" y="100" width="75" height="8" rx="4" fill="#006AE6"
                                fill-opacity="0.1" />
                            <circle cx="40" cy="135" r="16" fill="#006AE6" fill-opacity="0.15" />
                            <rect x="68" y="131" width="38" height="8" rx="4" fill="#006AE6"
                                fill-opacity="0.15" />
                        </g>

                        
                        <g transform="translate(180, 30) rotate(10)">
                            <path d="M75 25 L145 55 L75 85 L5 55 Z" fill="white" fill-opacity="0.98"
                                style="filter: drop-shadow(0 15px 25px rgba(0,0,0,0.12));" />
                            <path d="M30 65 L30 110 Q75 135 120 110 L120 65" fill="white" fill-opacity="0.8" />
                            <path d="M135 50 L135 95" stroke="white" stroke-width="4" stroke-linecap="round" />
                            <circle cx="135" cy="105" r="8" fill="white" />
                        </g>

                        
                        <g transform="translate(190, 170) rotate(-6)">
                            <rect x="0" y="0" width="150" height="120" rx="16" fill="white"
                                fill-opacity="0.95" style="filter: drop-shadow(0 20px 30px rgba(0,0,0,0.15));" />
                            <rect x="25" y="65" width="22" height="35" rx="6" fill="#006AE6"
                                fill-opacity="0.3" />
                            <rect x="64" y="45" width="22" height="55" rx="6" fill="#006AE6"
                                fill-opacity="0.6" />
                            <rect x="103" y="20" width="22" height="80" rx="6" fill="#006AE6" />
                            <path d="M36 50 L75 30 L114 10" stroke="#f79009" stroke-width="4" stroke-linecap="round"
                                stroke-linejoin="round" fill="none" />
                            <circle cx="36" cy="50" r="5" fill="#f79009" />
                            <circle cx="75" cy="30" r="5" fill="#f79009" />
                            <circle cx="114" cy="10" r="5" fill="#f79009" />
                        </g>

                        
                        <path d="M40 220 L46 238 L64 244 L46 250 L40 268 L34 250 L16 244 L34 238 Z" fill="white"
                            fill-opacity="0.9" />
                        <path d="M320 80 L324 92 L336 96 L324 100 L320 112 L316 100 L304 96 L316 92 Z" fill="white"
                            fill-opacity="0.7" />
                        <circle cx="80" cy="40" r="6" fill="white" fill-opacity="0.6" />
                        <circle cx="310" cy="280" r="8" fill="white" fill-opacity="0.5" />
                    </svg>

                    <div class="position-relative z-index-1">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center gap-4 mb-6">
                                    <div class="avatar-initial">
                                        {{ strtoupper(substr($mahasiswa->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-white opacity-75 fs-5 mb-1 fw-medium tracking-wide">
                                            {{ $sapaan }},</div>
                                        <h1 class="text-white fw-bolder mb-0 display-6" style="letter-spacing: -0.5px;">
                                            {{ $mahasiswa->nama }}
                                        </h1>
                                    </div>
                                </div>

                                <p class="text-white opacity-90 fs-5 mb-8"
                                    style="line-height: 1.6; max-width: 600px; font-weight: 300;">
                                    Selamat datang di portal Tracer Study Universitas Nurul Jadid. Partisipasi Anda sangat
                                    berarti untuk meningkatkan kualitas pendidikan dan relevansi kurikulum kami.
                                </p>

                                <div class="d-flex flex-wrap gap-4 align-items-center">
                                    @if ($sudahMengisi)
                                        <a href="{{ route('mahasiswa.tracer.index') }}"
                                            class="btn btn-light text-primary fw-bolder px-8 py-4 rounded-pill shadow-sm fs-6 hover-elevate-up">
                                            <i class="fas fa-eye me-2"></i> Lihat Jawaban Saya
                                        </a>
                                        <div
                                            class="d-flex align-items-center text-white bg-white bg-opacity-10 px-5 py-3 rounded-pill border border-white border-opacity-25">
                                            <i class="fas fa-check-circle text-success fs-4 me-2"></i>
                                            <span class="fw-semibold tracking-wide">Kuesioner Selesai</span>
                                        </div>
                                    @elseif ($kuesionerAktif)
                                        <a href="{{ route('mahasiswa.tracer.index') }}"
                                            class="btn btn-light text-primary fw-bolder px-8 py-4 rounded-pill shadow-sm fs-6 hover-elevate-up">
                                            <i class="fas fa-play me-2"></i> Mulai Kuesioner
                                        </a>
                                        <div class="d-flex align-items-center text-white opacity-75 fw-medium">
                                            <i class="fas fa-stopwatch fs-4 me-2"></i>
                                            <span>Butuh waktu ± 10 menit</span>
                                        </div>
                                    @else
                                        <button
                                            class="btn btn-light text-muted fw-bolder px-8 py-4 rounded-pill shadow-sm fs-6 hover-elevate-up"
                                            disabled style="opacity: 0.8;">
                                            <i class="fas fa-lock me-2"></i> Belum Tersedia
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card glass-card position-relative z-index-2 mx-auto mb-10"
                    style="width: 96%; max-width: 1200px; padding: 0.5rem; box-shadow: 0 16px 32px rgba(0, 106, 230, 0.08);">
                    <div class="row g-0">
                        <div class="col-md-4 border-md-end">
                            <div class="info-chip p-4">
                                <div class="icon-box"
                                    style="background: var(--primary-soft); color: var(--primary-color);">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div>
                                    <div class="text-muted fs-8 fw-bolder text-uppercase tracking-wider mb-1">Nomor Induk
                                        Mahasiswa (NIM)</div>
                                    <div class="text-main fs-4 fw-bolder">{{ $mahasiswa->nim }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 border-md-end">
                            <div class="info-chip p-4">
                                <div class="icon-box"
                                    style="background: var(--success-soft); color: var(--success-color);">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="text-muted fs-8 fw-bolder text-uppercase tracking-wider mb-1">Program Studi
                                    </div>
                                    <div class="text-main fs-4 fw-bolder text-truncate"
                                        title="{{ $mahasiswa->prodi ? $mahasiswa->prodi->nama_prodi : 'Tracer Study UNUJA' }}">
                                        {{ $mahasiswa->prodi ? $mahasiswa->prodi->nama_prodi : 'Tracer Study UNUJA' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-chip p-4">
                                <div class="icon-box" style="background: var(--info-soft); color: var(--info-color);">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <div class="text-muted fs-8 fw-bolder text-uppercase tracking-wider mb-1">Masa Berlaku
                                    </div>
                                    <div class="text-main fs-5 fw-bolder">
                                        {{ $kuesionerAktif && $kuesionerAktif->tgl_mulai ? \Carbon\Carbon::parse($kuesionerAktif->tgl_mulai)->locale('id')->translatedFormat('d F Y') . ' - ' . \Carbon\Carbon::parse($kuesionerAktif->tgl_selesai)->locale('id')->translatedFormat('d F Y') : 'Belum Tersedia' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card glass-card status-banner mb-10 {{ $sudahMengisi ? 'status-done' : 'status-pending' }}">
                    <div
                        class="p-5 p-md-6 d-flex flex-column flex-md-row align-items-start align-items-md-center gap-4 gap-md-6 w-100">

                        
                        <div class="d-flex align-items-center gap-4 w-100 w-md-auto">
                            <div class="status-icon flex-shrink-0">
                                <i class="fas {{ $sudahMengisi ? 'fa-check-double' : 'fa-hourglass-half' }}"></i>
                            </div>

                            
                            <h3 class="fw-bolder text-main m-0 fs-3 d-md-none">
                                @if ($sudahMengisi)
                                    Data Tersimpan
                                @elseif ($kuesionerAktif)
                                    Selesaikan Kuesioner
                                @else
                                    Belum Tersedia
                                @endif
                            </h3>
                        </div>

                        <div class="flex-grow-1 text-start">
                            
                            <h3 class="fw-bolder text-main mb-2 fs-2 d-none d-md-block">
                                @if ($sudahMengisi)
                                    Luar biasa! Data Anda telah tersimpan.
                                @elseif ($kuesionerAktif)
                                    Selesaikan kuesioner Tracer Study Anda
                                @else
                                    Belum ada kuesioner aktif
                                @endif
                            </h3>
                            <p class="text-muted fs-6 fs-md-5 mb-4 mt-2 mt-md-0"
                                style="max-width: 700px; line-height: 1.6;">
                                @if ($sudahMengisi)
                                    Terima kasih telah berpartisipasi pada Tracer Study ini. Anda tetap dapat
                                    memperbarui jawaban sebelum akses ditutup.
                                @elseif ($kuesionerAktif)
                                    Pengisian Tracer Study sedang
                                    berlangsung. Pastikan Anda mengisi kuesioner sebelum batas waktu agar data tercatat.
                                @else
                                    Mohon tunggu informasi lebih lanjut dari pihak kampus atau kembali lagi nanti saat
                                    kuesioner dibuka.
                                @endif
                            </p>

                            @if ($kuesionerAktif)
                                <div class="d-flex flex-column align-items-start">
                                    <div class="d-flex justify-content-between w-100 mb-2" style="max-width: 400px;">
                                        <span class="fs-7 fw-bold text-muted">Progres Pengisian</span>
                                        <span
                                            class="fs-7 fw-bolder {{ $sudahMengisi ? 'text-success' : 'text-warning' }}">
                                            {{ $sudahMengisi ? '100%' : '0%' }}
                                        </span>
                                    </div>
                                    <div class="progress-track" style="max-width: 400px;">
                                        <div class="progress-fill"
                                            style="width: {{ $sudahMengisi ? '100%' : '0%' }}; background: {{ $sudahMengisi ? 'var(--success-color)' : 'var(--warning-color)' }};">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="text-center text-md-end ms-md-auto mt-2 mt-md-0 flex-shrink-0 w-100 w-md-auto">
                            @if ($sudahMengisi)
                                <a href="{{ route('mahasiswa.tracer.index') }}"
                                    class="btn btn-success fw-bolder px-8 py-3 rounded-pill shadow-sm fs-6 w-100 w-md-auto">
                                    <i class="fas fa-eye me-2"></i> Lihat Detail
                                </a>
                            @elseif ($kuesionerAktif)
                                <a href="{{ route('mahasiswa.tracer.index') }}"
                                    class="btn btn-warning fw-bolder px-8 py-3 rounded-pill shadow-sm fs-6 text-white w-100 w-md-auto"
                                    style="background: var(--warning-color);">
                                    <i class="fas fa-pen me-2"></i> Isi Sekarang
                                </a>
                            @else
                                <span
                                    class="btn btn-secondary fw-bolder px-8 py-3 rounded-pill disabled fs-6 w-100 w-md-auto">
                                    Belum Tersedia
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                
                <div class="d-flex align-items-center mb-6">
                    <h2 class="fw-bolder text-main m-0 fs-2">Panduan Pengisian</h2>
                    <div class="ms-4 flex-grow-1" style="height: 1px; background: var(--border-color);"></div>
                </div>

                
                <div class="row g-5 mb-10">
                    @php
                        $langkah = [
                            [
                                'icon' => 'fa-laptop-code',
                                'color' => 'primary',
                                'judul' => 'Akses Kuesioner',
                                'deskripsi' => 'Klik tombol Mulai Kuesioner untuk membuka formulir.',
                            ],
                            [
                                'icon' => 'fa-clipboard-list',
                                'color' => 'warning',
                                'judul' => 'Lengkapi Data',
                                'deskripsi' => 'Isi pertanyaan seputar karir dan pengalaman Anda dengan jujur.',
                            ],
                            [
                                'icon' => 'fa-check-circle',
                                'color' => 'success',
                                'judul' => 'Simpan & Selesai',
                                'deskripsi' => 'Kirim jawaban Anda. Data akan tersimpan dengan aman.',
                            ],
                        ];
                    @endphp

                    @foreach ($langkah as $i => $step)
                        <div class="col-lg-4 position-relative">
                            <div class="card shadow-sm border border-dashed border-{{ $step['color'] }} bg-light-{{ $step['color'] }} h-100 p-6 p-md-8 position-relative overflow-hidden hover-elevate-up d-flex flex-column"
                                style="z-index: 1; border-radius: 24px;">

                                
                                <div class="position-absolute"
                                    style="bottom: -20px; right: 0px; font-size: 8rem; font-weight: 900; color: var(--text-muted); opacity: 0.05; line-height: 1; z-index: -1;">
                                    0{{ $i + 1 }}
                                </div>

                                <div class="d-flex align-items-center mb-6">
                                    <div class="d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                                        style="width: 60px; height: 60px; border-radius: 18px; background: var(--{{ $step['color'] }}-color); font-size: 1.6rem; box-shadow: 0 10px 20px rgba(0,0,0,0.08);">
                                        <i class="fas {{ $step['icon'] }}"></i>
                                    </div>
                                    <div class="ms-4">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle me-2"
                                                style="width: 6px; height: 6px; background: var(--{{ $step['color'] }}-color);">
                                            </div>
                                            <div class="text-uppercase fw-bold text-muted"
                                                style="font-size: 0.75rem; letter-spacing: 1.5px;">Langkah
                                                0{{ $i + 1 }}</div>
                                        </div>
                                        <h4 class="fw-bolder text-main m-0 fs-3">{{ $step['judul'] }}</h4>
                                    </div>
                                </div>

                                <p class="text-muted fs-6 mb-0 flex-grow-1" style="line-height: 1.7;">
                                    {{ $step['deskripsi'] }}
                                </p>
                            </div>

                            
                            @if ($i < count($langkah) - 1)
                                <div class="position-absolute d-none d-lg-flex align-items-center justify-content-center bg-white"
                                    style="top: 50%; right: -25px; transform: translateY(-50%); width: 50px; height: 50px; border-radius: 50%; z-index: 10; box-shadow: 0 8px 20px rgba(0,0,0,0.08); border: 1px solid var(--border-color);">
                                    <i class="fas fa-chevron-right text-muted fs-4"
                                        style="animation: pulse-arrow 2s infinite ease-in-out;"></i>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
        @include('layouts.footer')
    </div>
@endsection
