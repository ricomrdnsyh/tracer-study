@extends('layouts.main')

@section('title', 'Dashboard Admin')

@section('css')
    <style>
        /* Modern Premium Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulseGlow {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        .animate-fade-in-up {
            opacity: 0;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .delay-100 {
            animation-delay: 100ms;
        }

        .delay-200 {
            animation-delay: 200ms;
        }

        .delay-300 {
            animation-delay: 300ms;
        }

        .delay-400 {
            animation-delay: 400ms;
        }

        .delay-500 {
            animation-delay: 500ms;
        }

        /* Premium Greeting Card with Glassmorphism */
        .dashboard-greeting {
            background: linear-gradient(120deg, #1A1A2E 0%, #16213E 50%, #0F3460 100%);
            border-radius: 1.25rem;
            overflow: hidden;
            position: relative;
            box-shadow: 0 15px 35px rgba(15, 52, 96, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .dash-hero-pattern {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.5;
            z-index: 0;
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
            z-index: 0;
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

        /* Simple Glassmorphism Select2 */
        .select2-glass-simple .select2-container--bootstrap5 .select2-selection--single {
            background: transparent !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: none !important;
            height: auto;
            pointer-events: none;
            /* Let the pill handle the click */
        }

        .select2-glass-simple .select2-container--bootstrap5 .select2-selection--single .select2-selection__rendered {
            color: #ffffff !important;
            font-weight: 600;
            padding-left: 0;
            padding-right: 0 !important;
        }

        .select2-glass-simple .select2-container--bootstrap5 .select2-selection--single .select2-selection__arrow {
            display: none !important;
            /* Hide default arrow */
        }

        .filter-pill:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
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

        .dashboard-greeting .greeting-content {
            position: relative;
            z-index: 1;
        }

        .glass-box {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            animation: pulseGlow 2s infinite;
        }

        /* Stat Cards Enhancements */
        .stat-card {
            border: none;
            border-radius: 1rem;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            background: #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        }

        [data-bs-theme="dark"] .stat-card {
            background: #1e1e2d;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .stat-card .stat-icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-icon i {
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon i {
            transform: scale(1.1);
        }

        /* Glowing icon backgrounds */
        .icon-glow-primary {
            box-shadow: 0 0 15px rgba(0, 158, 247, 0.3);
        }

        .icon-glow-success {
            box-shadow: 0 0 15px rgba(80, 205, 137, 0.3);
        }

        .icon-glow-info {
            box-shadow: 0 0 15px rgba(114, 57, 234, 0.3);
        }

        .icon-glow-warning {
            box-shadow: 0 0 15px rgba(255, 199, 0, 0.3);
        }

        /* Chart Cards */
        .chart-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .chart-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }

        .chart-card .card-header {
            min-height: 70px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }

        #kt_chart_area,
        #kt_chart_donut,
        #kt_chart_prodi,
        #kt_chart_instansi {
            min-height: 300px;
        }

        .chart-empty {
            min-height: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #a1a5b7;
            animation: fadeInUp 0.5s ease-out;
        }

        .chart-empty i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.4;
            background: -webkit-linear-gradient(#a1a5b7, #e4e6ef);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Table Enhancements */
        .table-row-dashed tr {
            border-bottom: 1px dashed #e4e6ef !important;
            transition: background-color 0.2s ease;
        }

        .table-row-dashed tbody tr:hover {
            background-color: rgba(0, 158, 247, 0.02);
        }

        .table-row-dashed tbody tr:last-child {
            border-bottom: none !important;
        }

        /* Custom Hover Effect for Pintasan Cepat Buttons */
        .pintasan-btn {
            transition: all 0.3s ease;
        }

        .pintasan-btn.pintasan-primary:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.08) !important;
            border-color: var(--bs-primary) !important;
            border-style: dashed !important;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.15);
        }
        .pintasan-btn.pintasan-primary:hover .pintasan-title {
            color: var(--bs-primary) !important;
        }

        .pintasan-btn.pintasan-info:hover {
            background-color: rgba(var(--bs-info-rgb), 0.08) !important;
            border-color: var(--bs-info) !important;
            border-style: dashed !important;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(var(--bs-info-rgb), 0.15);
        }
        .pintasan-btn.pintasan-info:hover .pintasan-title {
            color: var(--bs-info) !important;
        }

        .pintasan-btn.pintasan-success:hover {
            background-color: rgba(var(--bs-success-rgb), 0.08) !important;
            border-color: var(--bs-success) !important;
            border-style: dashed !important;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
        }
        .pintasan-btn.pintasan-success:hover .pintasan-title {
            color: var(--bs-success) !important;
        }

        /* Dark Mode adjustments for Pintasan Cepat Hover */
        [data-bs-theme="dark"] .pintasan-btn.pintasan-primary:hover {
            background-color: rgba(0, 158, 247, 0.15) !important;
            border-color: rgba(0, 158, 247, 0.4) !important;
        }
        [data-bs-theme="dark"] .pintasan-btn.pintasan-primary:hover .pintasan-title {
            color: #009ef7 !important;
        }

        [data-bs-theme="dark"] .pintasan-btn.pintasan-info:hover {
            background-color: rgba(114, 57, 234, 0.15) !important;
            border-color: rgba(114, 57, 234, 0.4) !important;
        }
        [data-bs-theme="dark"] .pintasan-btn.pintasan-info:hover .pintasan-title {
            color: #7239ea !important;
        }

        [data-bs-theme="dark"] .pintasan-btn.pintasan-success:hover {
            background-color: rgba(80, 205, 137, 0.15) !important;
            border-color: rgba(80, 205, 137, 0.4) !important;
        }
        [data-bs-theme="dark"] .pintasan-btn.pintasan-success:hover .pintasan-title {
            color: #50cd89 !important;
        }
    </style>
@endsection

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">

            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    <div class="card dashboard-greeting shadow-sm mb-8 animate-fade-in-up delay-100 border-0 p-6 p-lg-10"
                        style="border-radius: 1.25rem;">
                        <div class="dash-hero-pattern"></div>
                        <svg class="hero-illus d-none d-lg-block" width="380" height="380" viewBox="0 0 380 380"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="190" cy="190" r="140" fill="white" fill-opacity="0.05" />
                            <circle cx="190" cy="190" r="100" fill="white" fill-opacity="0.1" />
                            <g transform="translate(50, 70) rotate(-12)">
                                <rect x="0" y="0" width="130" height="170" rx="16" fill="white"
                                    fill-opacity="0.95" style="filter: drop-shadow(0 12px 24px rgba(0,0,0,0.15));" />
                                <rect x="24" y="32" width="50" height="12" rx="6" fill="#16213E"
                                    fill-opacity="0.15" />
                                <rect x="24" y="60" width="82" height="8" rx="4" fill="#16213E"
                                    fill-opacity="0.1" />
                                <rect x="24" y="80" width="60" height="8" rx="4" fill="#16213E"
                                    fill-opacity="0.1" />
                                <rect x="24" y="100" width="75" height="8" rx="4" fill="#16213E"
                                    fill-opacity="0.1" />
                                <circle cx="40" cy="135" r="16" fill="#16213E" fill-opacity="0.15" />
                                <rect x="68" y="131" width="38" height="8" rx="4" fill="#16213E"
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
                                <rect x="25" y="65" width="22" height="35" rx="6" fill="#16213E"
                                    fill-opacity="0.3" />
                                <rect x="64" y="45" width="22" height="55" rx="6" fill="#16213E"
                                    fill-opacity="0.6" />
                                <rect x="103" y="20" width="22" height="80" rx="6" fill="#16213E" />
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

                        <div class="position-relative" style="z-index: 1;">
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

                            <div class="d-flex align-items-center gap-4">
                                <div class="avatar-initial">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-white opacity-75 fs-5 mb-1 fw-medium tracking-wide">
                                        {{ $sapaan }},</div>
                                    <h1 class="text-white fw-bolder mb-0 display-6" style="letter-spacing: -0.5px;">
                                        {{ Auth::user()->name }}
                                    </h1>
                                </div>
                            </div>

                            <p class="text-white opacity-90 fs-5 mt-6 mb-6"
                                style="line-height: 1.6; max-width: 600px; font-weight: 300;">
                                Selamat datang di Dashboard Fakultas Tracer Study. Pantau partisipasi alumni, analitik data,
                                dan kelola kuesioner dengan mudah dalam satu tempat.
                            </p>

                            <div class="d-flex flex-wrap gap-4 align-items-center position-relative" style="z-index: 10;">
                                <form method="GET" action="{{ route('fakultas.dashboard') }}"
                                    id="filter_kuesioner_form">
                                    <div class="d-flex align-items-center text-white bg-white bg-opacity-10 px-5 py-2 rounded-pill border border-white border-opacity-25 select2-glass-simple filter-pill hover-elevate-up shadow-sm"
                                        style="backdrop-filter: blur(10px); cursor: pointer; transition: all 0.3s;"
                                        onclick="if(!event.target.closest('.select2-results')) $('#filter_kuesioner').select2('open');">
                                        <i class="fa-solid fa-filter fs-5 me-3 opacity-75"></i>
                                        <span
                                            class="fw-semibold tracking-wide me-2 opacity-75 d-none d-sm-inline">Filter:</span>
                                        <div style="min-width: 200px; max-width: 300px;">
                                            <select name="kuesioner_id" id="filter_kuesioner"
                                                class="form-select form-select-sm form-select-transparent text-white"
                                                data-control="select2" data-placeholder="Pilih Kuesioner"
                                                data-allow-clear="false">
                                                <option value="all" {{ !$selectedKuesioner ? 'selected' : '' }}>Semua
                                                    Kuesioner</option>
                                                @foreach ($kuesionerList as $k)
                                                    <option value="{{ $k->id_kuesioner }}"
                                                        {{ $selectedKuesioner && $selectedKuesioner->id_kuesioner == $k->id_kuesioner ? 'selected' : '' }}>
                                                        {{ $k->judul }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <i class="fa-solid fa-chevron-down ms-2 fs-7 opacity-75"></i>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="row g-5 g-xl-8 mb-8">
                        <div class="col-xl-3 col-md-6 animate-fade-in-up delay-200">
                            <div class="card stat-card shadow-sm h-100 border border-dashed border-primary bg-light-primary"
                                onclick="window.location.href='{{ route('admin.respon.index') }}'">
                                <div class="card-body d-flex align-items-center">
                                    <div class="stat-icon bg-body shadow-sm me-4 icon-glow-primary">
                                        <i class="fa-solid fa-graduation-cap fs-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-1">Total Alumni</div>
                                        <div class="text-gray-900 fw-bolder fs-2x">{{ number_format($statsData['kpi']['total_alumni'] ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 animate-fade-in-up delay-300">
                            <div class="card stat-card shadow-sm h-100 border border-dashed border-success bg-light-success"
                                onclick="window.location.href='{{ route('admin.respon.index') }}'">
                                <div class="card-body d-flex align-items-center">
                                    <div class="stat-icon bg-body shadow-sm me-4 icon-glow-success">
                                        <i class="fa-solid fa-user-check fs-2x text-success"></i>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-1">Total Responden</div>
                                        <div class="text-gray-900 fw-bolder fs-2x">{{ number_format($statsData['kpi']['total_responden'] ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 animate-fade-in-up delay-400">
                            <div class="card stat-card shadow-sm h-100 border border-dashed border-info bg-light-info">
                                <div class="card-body d-flex align-items-center">
                                    <div class="stat-icon bg-body shadow-sm me-4 icon-glow-info">
                                        <i class="fa-solid fa-percent fs-2x text-info"></i>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-1">Response Rate</div>
                                        <div class="text-gray-900 fw-bolder fs-2x">{{ number_format($statsData['kpi']['response_rate'] ?? 0, 1, ',', '.') }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 animate-fade-in-up delay-500">
                            <div class="card stat-card shadow-sm h-100 border border-dashed border-warning bg-light-warning">
                                <div class="card-body d-flex align-items-center">
                                    <div class="stat-icon bg-body shadow-sm me-4 icon-glow-warning">
                                        <i class="fa-solid fa-briefcase fs-2x text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-1">Rata-rata Waktu Tunggu</div>
                                        <div class="text-gray-900 fw-bolder fs-2x">{{ $statsData['kpi']['waktu_tunggu_bekerja'] ?? 0 }} <span class="fs-6 fw-semibold text-gray-600">Bulan</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-5 g-xl-8 mb-8 animate-fade-in-up delay-400">
                        <div class="col-12">
                            <div class="card shadow-sm border border-dashed border-dark" style="border-radius: 1.25rem;">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-primary bg-opacity-10 me-4 icon-glow-primary p-3 rounded-3"
                                                style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-bolt fs-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <span class="card-label fw-bolder fs-4 text-gray-900">Pintasan Cepat</span>
                                                <span class="text-gray-600 fw-semibold fs-7 d-block mt-1">Akses cepat ke
                                                    fitur utama tracer study</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pb-6">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <a href="{{ route('admin.statistik.index') }}"
                                                class="btn btn-outline btn-outline-dashed pintasan-btn pintasan-info p-7 d-flex align-items-center mb-0 w-100"
                                                style="border-radius: 1rem;">
                                                <i class="fa-solid fa-chart-pie fs-2x me-4 text-info"></i>
                                                <div class="text-start">
                                                    <span class="d-block fw-bold fs-5 text-gray-800 pintasan-title" style="transition: color 0.3s ease;">Statistik & Laporan</span>
                                                    <span class="d-block fw-semibold fs-7 text-gray-600 mt-1">Lihat
                                                        analitik keseluruhan</span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ route('admin.laporan.index') }}"
                                                class="btn btn-outline btn-outline-dashed pintasan-btn pintasan-success p-7 d-flex align-items-center mb-0 w-100"
                                                style="border-radius: 1rem;">
                                                <i class="fa-solid fa-file-word fs-2x me-4 text-success"></i>
                                                <div class="text-start">
                                                    <span class="d-block fw-bold fs-5 text-gray-800 pintasan-title" style="transition: color 0.3s ease;">Cetak Laporan</span>
                                                    <span class="d-block fw-semibold fs-7 text-gray-600 mt-1">Ekspor
                                                        hasil tracer ke Word</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border border-dashed border-dark animate-fade-in-up delay-500"
                        style="border-radius: 1.25rem; animation-delay: 600ms">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <div>
                                    <span class="card-label fw-bolder fs-4 text-gray-900">Responden Terbaru</span>
                                    <span class="text-gray-600 fw-semibold fs-7 d-block mt-1">Data responden tracer study
                                        terakhir yang masuk</span>
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <a href="#" class="btn btn-sm btn-light-primary">
                                    Lihat Semua
                                    <i class="fa-solid fa-arrow-right ms-2 fs-7"></i>
                                </a>
                            </div>
                        </div>
                        <div class="separator my-3"></div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-4">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="min-w-80px text-center">Aksi</th>
                                            <th class="min-w-150px">Mahasiswa</th>
                                            <th class="min-w-100px">NIM</th>
                                            <th class="min-w-150px d-none d-md-table-cell">Prodi</th>
                                            <th class="min-w-150px d-none d-lg-table-cell">Kuesioner</th>
                                            <th class="min-w-140px">Tanggal Isi</th>
                                            <th class="min-w-90px">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-bold text-gray-800">
                                        @forelse ($recentRespon as $r)
                                            <tr>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.respon.show', $r->id_respon) }}"
                                                        class="btn btn-sm btn-icon btn-light btn-active-light-info"
                                                        data-bs-toggle="tooltip" title="Lihat Detail Jawaban">
                                                        <i class="fa-solid fa-eye text-primary"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-40px symbol-circle me-3">
                                                            <span
                                                                class="symbol-label bg-primary bg-opacity-10 text-primary fw-bolder">
                                                                {{ strtoupper(substr($r->mahasiswa?->nama ?? '?', 0, 1)) }}
                                                            </span>
                                                        </div>
                                                        <div class="text-gray-800 fw-bolder">
                                                            {{ $r->mahasiswa?->nama ?? '-' }}</div>
                                                    </div>
                                                </td>
                                                <td class="text-gray-600">{{ $r->mahasiswa_id }}</td>
                                                <td class="d-none d-md-table-cell text-gray-600">
                                                    {{ $r->mahasiswa?->prodi?->nama_prodi ?? '-' }}
                                                </td>
                                                <td class="d-none d-lg-table-cell text-gray-600">
                                                    <span class="text-truncate d-block"
                                                        style="max-width: 220px;">{{ $r->kuesioner?->judul ?? '-' }}</span>
                                                </td>
                                                <td class="text-gray-600">
                                                    {{ \Carbon\Carbon::parse($r->tgl_isi)->translatedFormat('d F Y, H:i') }}
                                                    WIB
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">{{ $r->status }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-gray-500 py-8">
                                                    <i class="fa-solid fa-inbox fs-2x mb-3 d-block text-gray-300"></i>
                                                    <span class="fw-semibold">Belum ada responden yang masuk</span>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
        (function() {
            var $filterEl = $('#filter_kuesioner');
            var filterForm = document.getElementById('filter_kuesioner_form');
            if ($filterEl.length && filterForm) {
                $filterEl.on('change', function() {
                    filterForm.submit();
                });
            }

        })();
    </script>
@endsection
