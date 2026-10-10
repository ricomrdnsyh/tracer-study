@extends('layouts.main')

@section('title', 'Statistik')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/responsive.bootstrap.min.css') }}">
    <style>
        .stat-card-modern {
            border: 1px dashed rgba(0, 0, 0, 0.12);
            border-radius: 1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }

        [data-bs-theme="dark"] .stat-card-modern {
            background: #1e1e2d;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .stat-card-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .stat-icon-wrapper {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
        }

        .kemdikti-card {
            border: 1px dashed var(--bs-gray-400, #b5b5c3) !important;
            border-radius: 1.25rem !important;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        [data-bs-theme="dark"] .kemdikti-card {
            background: #1e1e2d;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        .table-kemdikti th {
            background-color: #5b67ec !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        [data-bs-theme="dark"] .table-kemdikti th {
            background-color: #4338ca !important;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(2px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
            border-radius: 1.25rem;
        }

        [data-bs-theme="dark"] .loading-overlay {
            background: rgba(30, 30, 45, 0.75);
        }

        .nav-pills .nav-link.active {
            background-color: #5b67ec !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(91, 103, 236, 0.35);
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-sizing: border-box !important;
            }

            #kt_app_sidebar,
            #kt_app_sidebar_toggle,
            #kt_app_header,
            #kt_app_footer,
            #kt_app_toolbar,
            .no-print,
            .d-print-none,
            .filter-container,
            #btn_refresh_stats,
            #btn_print_pdf,
            #search_rekap_prodi,
            .loading-overlay,
            #aspect_nav_pills,
            #aspect_dual_cards {
                display: none !important;
            }

            .d-print-block {
                display: block !important;
            }

            .d-print-flex {
                display: flex !important;
            }

            html,
            body {
                background: #ffffff !important;
                color: #181c32 !important;
                font-size: 11px !important;
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            #kt_app_root,
            #kt_app_page,
            #kt_app_wrapper,
            #kt_app_main,
            #kt_app_content,
            #kt_app_content_container,
            .app-root,
            .app-page,
            .app-wrapper,
            .app-main,
            .app-content,
            .app-container {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                display: block !important;
                position: static !important;
                overflow: visible !important;
                height: auto !important;
                min-height: 0 !important;
            }

            .row:not(.kpi-row-container) {
                display: block !important;
                margin: 0 0 10px 0 !important;
                height: auto !important;
                min-height: 0 !important;
            }

            .row:not(.kpi-row-container)>[class*="col-"] {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                flex: none !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin: 0 0 10px 0 !important;
                height: auto !important;
                min-height: 0 !important;
            }

            .kpi-row-container {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                gap: 6px !important;
                margin-bottom: 12px !important;
                width: 100% !important;
            }

            .kpi-row-container>div {
                flex: 1 1 0 !important;
                width: 20% !important;
                max-width: 20% !important;
                padding: 0 !important;
            }

            .stat-card-modern {
                padding: 8px 10px !important;
                border-radius: 6px !important;
                border: 1px dashed #b5b5c3 !important;
                box-shadow: none !important;
                background: #ffffff !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                height: auto !important;
                min-height: 0 !important;
            }

            .stat-card-modern .stat-icon-wrapper {
                width: 26px !important;
                height: 26px !important;
                border-radius: 4px !important;
            }

            .stat-card-modern .stat-icon-wrapper i {
                font-size: 0.9rem !important;
            }

            .stat-card-modern .fs-2x {
                font-size: 1.15rem !important;
                line-height: 1.2 !important;
                font-weight: 700 !important;
            }

            .stat-card-modern .fs-7 {
                font-size: 0.72rem !important;
            }

            .stat-card-modern .fs-8 {
                font-size: 0.62rem !important;
            }

            .mb-8,
            .my-8 {
                margin-bottom: 10px !important;
            }

            .mb-6,
            .my-6 {
                margin-bottom: 8px !important;
            }

            .mb-5,
            .my-5 {
                margin-bottom: 6px !important;
            }

            .mb-4,
            .my-4 {
                margin-bottom: 6px !important;
            }

            .mt-7,
            .mt-6,
            .mt-4 {
                margin-top: 6px !important;
            }

            .mt-3 {
                margin-top: 4px !important;
            }

            .my-3 {
                margin-top: 4px !important;
                margin-bottom: 4px !important;
            }

            .p-6,
            .p-5,
            .p-4 {
                padding: 6px 10px !important;
            }

            .card,
            .kemdikti-card,
            .chart-box {
                box-shadow: none !important;
                border: 1px dashed #b5b5c3 !important;
                border-radius: 8px !important;
                margin-bottom: 12px !important;
                background: #ffffff !important;
                height: auto !important;
                min-height: 0 !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }

            .h-100 {
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
            }

            .card-header {
                padding: 8px 12px 4px 12px !important;
                min-height: 0 !important;
                border-bottom: 1px solid #f0f0f2 !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }

            .card-body {
                padding: 8px 12px 10px 12px !important;
                height: auto !important;
                min-height: 0 !important;
            }

            .kompetensi-aspect-print-card,
            .waktu-tunggu-card {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                border: 1px dashed #b5b5c3 !important;
                border-radius: 8px !important;
                background: #ffffff !important;
                padding: 10px 12px !important;
                margin-bottom: 12px !important;
                height: auto !important;
                min-height: 0 !important;
                display: block !important;
            }

            div[id^="chart_"] {
                min-height: 0 !important;
                height: auto !important;
                margin-bottom: 4px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                overflow: visible !important;
            }

            .apexcharts-canvas {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 auto !important;
            }

            .apexcharts-canvas svg.apexcharts-svg {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                display: block !important;
                margin: 0 auto !important;
            }

            .table {
                font-size: 9.5px !important;
                width: 100% !important;
                margin-bottom: 0 !important;
            }

            .table th,
            .table td {
                padding: 3px 6px !important;
            }

            .table-responsive {
                overflow: visible !important;
                height: auto !important;
                min-height: 0 !important;
            }

            thead {
                display: table-header-group !important;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .table-kemdikti th {
                background-color: #5b67ec !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            [data-bs-theme="dark"] body,
            [data-bs-theme="dark"] .card,
            [data-bs-theme="dark"] .kemdikti-card,
            [data-bs-theme="dark"] .stat-card-modern,
            [data-bs-theme="dark"] .kompetensi-aspect-print-card,
            [data-bs-theme="dark"] .waktu-tunggu-card {
                background: #ffffff !important;
                color: #181c32 !important;
                border-color: #dcdfe6 !important;
            }

            [data-bs-theme="dark"] .text-gray-900,
            [data-bs-theme="dark"] .text-gray-800,
            [data-bs-theme="dark"] .text-gray-700 {
                color: #181c32 !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">

            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mb-4">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-light-primary fw-bold px-3 py-2">
                                    <i class="fa-solid fa-chart-line text-primary me-1"></i> Wawasan Alumni
                                </span>
                            </div>
                            <h1 class="text-gray-900 fw-bolder fs-1 mb-1 mt-2">Statistik Tracer Study</h1>
                            <span class="text-gray-600 fw-semibold fs-6">
                                Pantau dan eksplorasi jejak karier serta kompetensi alumni melalui visualisasi data
                                interaktif.
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 no-print">
                            <button type="button" class="btn btn-sm btn-primary fw-bold" id="btn_print_pdf">
                                <i class="fa-solid fa-print me-2"></i>Cetak / Ekspor PDF
                            </button>
                        </div>
                    </div>

                    <div class="d-none d-print-block mb-4 p-3 border border-dashed border-gray-400 rounded-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center fs-8 text-gray-700">
                            <div><i class="fa-solid fa-file-lines text-primary me-1"></i><strong>Dokumen:</strong> Laporan
                                Statistik Tracer Study Standar Kemdiktisaintek</div>
                            <div><i class="fa-solid fa-clock text-gray-500 me-1"></i><strong>Waktu Ekspor:</strong>
                                {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</div>
                        </div>
                    </div>

                    <div class="card shadow-sm border border-dashed border-primary mb-8 filter-container"
                        style="border-radius: 1.25rem;">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="text-primary fw-bolder mb-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-filter text-primary"></i> Filter Analitik
                                </h5>
                                <button type="button"
                                    class="btn btn-link btn-color-muted btn-active-color-primary p-0 fs-7 fw-bold"
                                    id="btn_reset_filter">
                                    <i class="fa-solid fa-arrows-rotate fs-8 me-1"></i> Reset Filter
                                </button>
                            </div>

                            <div class="row g-4 align-items-end">

                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold fs-7 mb-2 text-gray-700">Kuesioner Tracer:</label>
                                    <select id="filter_kuesioner" class="form-select form-select-sm" data-control="select2"
                                        data-placeholder="Semua Kuesioner" data-allow-clear="true">
                                        <option value="all">Semua Kuesioner</option>
                                        @foreach ($kuesionerList as $k)
                                            <option value="{{ $k->id_kuesioner }}">{{ $k->judul }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold fs-7 mb-2 text-gray-700">Tahun Kelulusan:</label>
                                    <select id="filter_akademik" class="form-select form-select-sm" data-control="select2"
                                        data-placeholder="Semua Tahun" data-allow-clear="true">
                                        <option value="all">Semua Tahun Akademik</option>
                                        @foreach ($tahunAkademikList as $ta)
                                            <option value="{{ $ta->id_smt }}">{{ $ta->nm_smt }} ({{ $ta->id_smt }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if ($isFakultas)
                                    <input type="hidden" id="filter_fakultas" value="{{ $userFakultasId }}">
                                @else
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                        <label class="form-label fw-bold fs-7 mb-2 text-gray-700">Fakultas:</label>
                                        <select id="filter_fakultas" class="form-select form-select-sm"
                                            data-control="select2" data-placeholder="Semua Fakultas"
                                            data-allow-clear="true">
                                            <option value="all">Semua Fakultas</option>
                                            @foreach ($fakultasList as $f)
                                                <option value="{{ $f->id_fakultas }}">
                                                    {{ $f->nama_fakultas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div
                                    class="{{ $isFakultas ? 'col-xl-5 col-lg-6' : 'col-xl-2 col-lg-3' }} col-md-6 col-sm-12">
                                    <label class="form-label fw-bold fs-7 mb-2 text-gray-700">Program Studi:</label>
                                    <select id="filter_prodi" class="form-select form-select-sm" data-control="select2"
                                        data-placeholder="Semua Prodi" data-allow-clear="true">
                                        <option value="all">Semua Program Studi</option>
                                        @foreach ($prodiList as $p)
                                            <option value="{{ $p->id_prodi }}" data-fakultas="{{ $p->fakultas_id }}">
                                                {{ $p->nama_prodi }} ({{ $p->jenjang ?? 'S1' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-2 col-lg-12 col-md-12 col-sm-12 d-flex justify-content-xl-end">
                                    <button type="button" class="btn btn-sm btn-primary fw-bold w-100"
                                        id="btn_apply_filter">
                                        <span class="indicator-label">
                                            <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                                        </span>
                                        <span class="indicator-progress d-none">
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            Memproses...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="empty_filter_state"
                        class="card text-center py-20 my-10 rounded-4 border border-dashed border-dark shadow-sm d-flex flex-column justify-content-center align-items-center"
                        style="min-height: 350px;">
                        <i class="fa-solid fa-chart-pie text-muted mb-6" style="font-size: 5rem;"></i>
                        <h3 class="text-gray-900 fw-bolder fs-1 mb-3">Pilih Filter untuk Menampilkan Statistik</h3>
                        <p class="text-gray-500 fs-5 mb-0">Silakan sesuaikan filter di atas untuk memuat data statistik
                            tracer study.</p>
                    </div>

                    <div id="statistics_container" style="display: none;">

                        <div class="position-relative">
                            <div class="loading-overlay" id="kpi_loading">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>

                            <div class="row g-4 g-xl-5 mb-8 kpi-row-container">

                                <div class="col-xl col-md-6 col-12">
                                    <div class="card stat-card-modern p-5 h-100 border-primary bg-light-primary">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="text-gray-600 fw-bold fs-7">Total Alumni</span>
                                            <div class="stat-icon-wrapper bg-white shadow-sm text-primary">
                                                <i class="fa-solid fa-graduation-cap fs-3"></i>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <div class="text-gray-900 fw-bolder fs-2x" id="kpi_total_alumni">
                                                {{ number_format($statsData['kpi']['total_alumni'] ?? 0, 0, ',', '.') }}
                                            </div>
                                            <span class="text-gray-500 fs-7 fw-semibold">Target</span>
                                        </div>
                                        <div class="text-gray-500 fs-8 mt-1">Populasi kelulusan terdata</div>
                                    </div>
                                </div>

                                <div class="col-xl col-md-6 col-12">
                                    <div class="card stat-card-modern p-5 h-100 border-success bg-light-success">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="text-gray-600 fw-bold fs-7">Total Responden</span>
                                            <div class="stat-icon-wrapper bg-white shadow-sm text-success">
                                                <i class="fa-solid fa-user-check fs-3"></i>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <div class="text-gray-900 fw-bolder fs-2x" id="kpi_total_responden">
                                                {{ number_format($statsData['kpi']['total_responden'] ?? 0, 0, ',', '.') }}
                                            </div>
                                            <span class="text-gray-500 fs-7 fw-semibold">Selesai</span>
                                        </div>
                                        <div class="text-gray-500 fs-8 mt-1">Alumni mengisi kuesioner</div>
                                    </div>
                                </div>

                                <div class="col-xl col-md-6 col-12">
                                    <div class="card stat-card-modern p-5 h-100 border-info bg-light-info">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="text-gray-600 fw-bold fs-7">Response Rate</span>
                                            <div class="stat-icon-wrapper bg-white shadow-sm text-info">
                                                <i class="fa-solid fa-percent fs-3"></i>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <div class="text-gray-900 fw-bolder fs-2x" id="kpi_response_rate">
                                                {{ $statsData['kpi']['response_rate'] ?? 0 }}%
                                            </div>
                                        </div>
                                        <div class="progress h-6px w-100 bg-white mt-2 rounded">
                                            <div class="progress-bar bg-info" id="kpi_progress_rate" role="progressbar"
                                                style="width: {{ min(100, $statsData['kpi']['response_rate'] ?? 0) }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl col-md-6 col-12">
                                    <div class="card stat-card-modern p-5 h-100 border-warning bg-light-warning">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="text-gray-600 fw-bold fs-7">Keselarasan Relevan</span>
                                            <div class="stat-icon-wrapper bg-white shadow-sm text-warning">
                                                <i class="fa-solid fa-bullseye fs-3"></i>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <div class="text-gray-900 fw-bolder fs-2x" id="kpi_keselarasan_rate">
                                                {{ $statsData['kpi']['keselarasan_rate'] ?? 0 }}%
                                            </div>
                                        </div>
                                        <div class="text-gray-500 fs-8 mt-1">Bidang studi & pekerjaan sesuai</div>
                                    </div>
                                </div>

                                <div class="col-xl col-md-6 col-12">
                                    <div class="card stat-card-modern p-5 h-100 border-dark bg-light">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="text-gray-600 fw-bold fs-7">Rata-rata Waktu Tunggu</span>
                                            <div class="stat-icon-wrapper bg-white shadow-sm text-dark">
                                                <i class="fa-solid fa-business-time fs-3"></i>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <div class="text-gray-900 fw-bolder fs-2x" id="kpi_avg_waktu_tunggu">
                                                {{ $statsData['kpi']['avg_waktu_tunggu'] ?? 0 }}
                                            </div>
                                            <span class="text-gray-600 fs-7 fw-semibold">Bulan</span>
                                        </div>
                                        <div class="text-gray-500 fs-8 mt-1">Mendapatkan kerja pertama</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card kemdikti-card mb-8 shadow-sm">
                                <div
                                    class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="card-label fw-bolder fs-4 text-gray-900 mb-1">
                                            [F8] Jelaskan status Anda saat ini?
                                        </h4>
                                        <span class="text-gray-500 fs-8 fw-semibold">F8</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                        <span class="text-gray-900 fw-bolder fs-2" id="f8_total_responden">
                                            {{ number_format($statsData['status_aktivitas']['total_responden'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-6">
                                    <div id="chart_status_f8" style="min-height: 330px;"></div>

                                    <div class="table-responsive mt-4">
                                        <table class="table align-middle table-row-dashed table-kemdikti fs-7 gy-3 mb-0"
                                            id="table_f8">
                                            <thead>
                                                <tr class="fw-bolder text-white">
                                                    <th class="ps-4">Label</th>
                                                    <th class="text-center" style="width: 120px;">Value/Nilai</th>
                                                    <th class="text-end">Jumlah</th>
                                                    <th class="pe-4 text-end">Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-bold text-gray-700" id="tbody_f8">
                                                @if (isset($statsData['status_aktivitas']['table']))
                                                    @foreach ($statsData['status_aktivitas']['table'] as $row)
                                                        <tr>
                                                            <td class="ps-4 text-gray-800">{{ $row['label'] }}</td>
                                                            <td class="text-center text-gray-600">{{ $row['value'] }}</td>
                                                            <td class="text-end">
                                                                {{ number_format($row['jumlah'], 0, ',', '.') }} responden
                                                            </td>
                                                            <td class="pe-4 text-end">{{ $row['persentase'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr class="fw-bolder bg-light">
                                                    <td class="ps-4 text-gray-900">Total</td>
                                                    <td></td>
                                                    <td class="text-end text-gray-900" id="f8_table_total_count">
                                                        {{ number_format($statsData['status_aktivitas']['total_responden'] ?? 0, 0, ',', '.') }}
                                                        responden
                                                    </td>
                                                    <td class="pe-4 text-end text-gray-900">100%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>

                            <div class="card kemdikti-card mb-8 shadow-sm">
                                <div
                                    class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="card-label fw-bolder fs-4 text-gray-900 mb-0">
                                            Take home pay &ndash; rentang &amp; rata-rata (F505)
                                        </h4>
                                        <span class="text-gray-500 fs-8 fw-semibold">F505</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                        <span class="text-gray-900 fw-bolder fs-2" id="f505_total_responden">
                                            {{ number_format($statsData['take_home_pay']['total_responden'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-6">
                                    <div id="chart_take_home_pay" style="min-height: 330px;"></div>

                                    <div class="text-center my-3">
                                        <span
                                            class="badge badge-light-primary fs-6 fw-bold px-4 py-2 border border-primary border-dashed">
                                            Rata-rata: <span id="f505_rata_rata_badge"
                                                class="fw-bolder ms-1">{{ $statsData['take_home_pay']['rata_rata'] ?? 'Rp 0,00' }}</span>
                                        </span>
                                    </div>

                                    <div class="table-responsive mt-3">
                                        <table class="table align-middle table-row-dashed table-kemdikti fs-7 gy-3 mb-0"
                                            id="table_f505">
                                            <thead>
                                                <tr class="fw-bolder text-white">
                                                    <th class="ps-4">Label</th>
                                                    <th class="text-end">Jumlah</th>
                                                    <th class="pe-4 text-end">Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-bold text-gray-700" id="tbody_f505">
                                                @if (isset($statsData['take_home_pay']['table']))
                                                    @foreach ($statsData['take_home_pay']['table'] as $row)
                                                        <tr>
                                                            <td class="ps-4 text-gray-800">{{ $row['label'] }}</td>
                                                            <td class="text-end">
                                                                {{ number_format($row['jumlah'], 0, ',', '.') }} responden
                                                            </td>
                                                            <td class="pe-4 text-end">{{ $row['persentase'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr class="fw-bolder bg-light">
                                                    <td class="ps-4 text-gray-900">Total</td>
                                                    <td class="text-end text-gray-900" id="f505_table_total_count">
                                                        {{ number_format($statsData['take_home_pay']['total_responden'] ?? 0, 0, ',', '.') }}
                                                        responden
                                                    </td>
                                                    <td class="pe-4 text-end text-gray-900">100%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>

                            <div class="card kemdikti-card mb-8 shadow-sm">
                                <div
                                    class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="card-label fw-bolder fs-4 text-gray-900 mb-1">
                                            [F1201] Sebutkan sumber dana dalam pembiayaan kuliah? (bukan ketika Studi
                                            Lanjut)
                                        </h4>
                                        <span class="text-gray-500 fs-8 fw-semibold">F1201</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                        <span class="text-gray-900 fw-bolder fs-2" id="f1201_total_responden">
                                            {{ number_format($statsData['sumber_dana']['total_responden'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-6">
                                    <div id="chart_sumber_dana" style="min-height: 340px;"></div>

                                    <div class="table-responsive mt-4">
                                        <table class="table align-middle table-row-dashed table-kemdikti fs-7 gy-3 mb-0"
                                            id="table_f1201">
                                            <thead>
                                                <tr class="fw-bolder text-white">
                                                    <th class="ps-4">Label</th>
                                                    <th class="text-center" style="width: 120px;">Value/Nilai</th>
                                                    <th class="text-end">Jumlah</th>
                                                    <th class="pe-4 text-end">Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-bold text-gray-700" id="tbody_f1201">
                                                @if (isset($statsData['sumber_dana']['table']))
                                                    @foreach ($statsData['sumber_dana']['table'] as $row)
                                                        <tr>
                                                            <td class="ps-4 text-gray-800">{{ $row['label'] }}</td>
                                                            <td class="text-center text-gray-600">{{ $row['value'] }}</td>
                                                            <td class="text-end">
                                                                {{ number_format($row['jumlah'], 0, ',', '.') }} responden
                                                            </td>
                                                            <td class="pe-4 text-end">{{ $row['persentase'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr class="fw-bolder bg-light">
                                                    <td class="ps-4 text-gray-900">Total</td>
                                                    <td></td>
                                                    <td class="text-end text-gray-900" id="f1201_table_total_count">
                                                        {{ number_format($statsData['sumber_dana']['total_responden'] ?? 0, 0, ',', '.') }}
                                                        responden
                                                    </td>
                                                    <td class="pe-4 text-end text-gray-900">100%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>

                            <div class="card kemdikti-card mb-8 shadow-sm">
                                <div
                                    class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="card-label fw-bolder fs-4 text-gray-900 mb-1">
                                            [F1101] Apa jenis perusahaan/intansi/institusi tempat anda bekerja sekarang?
                                        </h4>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-gray-500 fs-8 fw-semibold">F1101</span>
                                            <span class="badge badge-light text-gray-600 fw-bold fs-8 border">Filter: F8 =
                                                1</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                        <span class="text-gray-900 fw-bolder fs-2" id="f1101_total_responden">
                                            {{ number_format($statsData['jenis_instansi']['total_responden'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-6">
                                    <div id="chart_jenis_instansi" style="min-height: 340px;"></div>

                                    <div class="table-responsive mt-4">
                                        <table class="table align-middle table-row-dashed table-kemdikti fs-7 gy-3 mb-0"
                                            id="table_f1101">
                                            <thead>
                                                <tr class="fw-bolder text-white">
                                                    <th class="ps-4">Label</th>
                                                    <th class="text-center" style="width: 120px;">Value/Nilai</th>
                                                    <th class="text-end">Jumlah</th>
                                                    <th class="pe-4 text-end">Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-bold text-gray-700" id="tbody_f1101">
                                                @if (isset($statsData['jenis_instansi']['table']))
                                                    @foreach ($statsData['jenis_instansi']['table'] as $row)
                                                        <tr>
                                                            <td class="ps-4 text-gray-800">{{ $row['label'] }}</td>
                                                            <td class="text-center text-gray-600">{{ $row['value'] }}</td>
                                                            <td class="text-end">
                                                                {{ number_format($row['jumlah'], 0, ',', '.') }} responden
                                                            </td>
                                                            <td class="pe-4 text-end">{{ $row['persentase'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr class="fw-bolder bg-light">
                                                    <td class="ps-4 text-gray-900">Total</td>
                                                    <td></td>
                                                    <td class="text-end text-gray-900" id="f1101_table_total_count">
                                                        {{ number_format($statsData['jenis_instansi']['total_responden'] ?? 0, 0, ',', '.') }}
                                                        responden
                                                    </td>
                                                    <td class="pe-4 text-end text-gray-900">100%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>

                            <div class="row g-6 mb-8">

                                <div class="col-xl-6 col-lg-12">
                                    <div class="card kemdikti-card waktu-tunggu-card shadow-sm mb-4 h-100">
                                        <div
                                            class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                            <div>
                                                <h4 class="card-label fw-bolder fs-5 text-gray-900 mb-1">
                                                    Mendapatkan pekerjaan &ndash; rentang bulan &amp; rata-rata (F502)
                                                </h4>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-gray-500 fs-8 fw-semibold">F502</span>
                                                    <span
                                                        class="badge badge-light text-gray-600 fw-bold fs-8 border">Filter:
                                                        F8 = 1</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                                <span class="text-gray-900 fw-bolder fs-3" id="f502_bekerja_total">
                                                    {{ number_format($statsData['waktu_tunggu_bekerja']['total_responden'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body pt-2 pb-6">
                                            <div id="chart_waktu_tunggu_bekerja" style="min-height: 280px;"></div>

                                            <div class="text-center my-3">
                                                <span
                                                    class="badge badge-light-primary fs-7 fw-bold px-3 py-1 border border-primary border-dashed">
                                                    Rata-rata: <span id="f502_bekerja_avg"
                                                        class="fw-bolder ms-1">{{ $statsData['waktu_tunggu_bekerja']['rata_rata'] ?? '0,00 bulan' }}</span>
                                                </span>
                                            </div>

                                            <div class="table-responsive mt-3">
                                                <table
                                                    class="table align-middle table-row-dashed table-kemdikti fs-7 gy-2 mb-0"
                                                    id="table_f502_bekerja">
                                                    <thead>
                                                        <tr class="fw-bolder text-white">
                                                            <th class="ps-3">Label</th>
                                                            <th class="text-end">Jumlah</th>
                                                            <th class="pe-3 text-end">Persentase</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="fw-bold text-gray-700" id="tbody_f502_bekerja">
                                                        @if (isset($statsData['waktu_tunggu_bekerja']['table']))
                                                            @foreach ($statsData['waktu_tunggu_bekerja']['table'] as $row)
                                                                <tr>
                                                                    <td class="ps-3 text-gray-800">{{ $row['label'] }}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ number_format($row['jumlah'], 0, ',', '.') }}
                                                                        responden</td>
                                                                    <td class="pe-3 text-end">{{ $row['persentase'] }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-lg-12">
                                    <div class="card kemdikti-card waktu-tunggu-card shadow-sm mb-4 h-100">
                                        <div
                                            class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                            <div>
                                                <h4 class="card-label fw-bolder fs-5 text-gray-900 mb-1">
                                                    [F502] Memulai wiraswasta &ndash; rentang bulan &amp; rata-rata
                                                </h4>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-gray-500 fs-8 fw-semibold">F502</span>
                                                    <span
                                                        class="badge badge-light text-gray-600 fw-bold fs-8 border">Filter:
                                                        F8 = 3</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                                <span class="text-gray-900 fw-bolder fs-3" id="f502_wiraswasta_total">
                                                    {{ number_format($statsData['waktu_tunggu_wiraswasta']['total_responden'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body pt-2 pb-6">
                                            <div id="chart_waktu_tunggu_wiraswasta" style="min-height: 280px;"></div>

                                            <div class="text-center my-3">
                                                <span
                                                    class="badge badge-light-warning fs-7 fw-bold px-3 py-1 border border-warning border-dashed">
                                                    Rata-rata: <span id="f502_wiraswasta_avg"
                                                        class="fw-bolder ms-1">{{ $statsData['waktu_tunggu_wiraswasta']['rata_rata'] ?? '0,00 bulan' }}</span>
                                                </span>
                                            </div>

                                            <div class="table-responsive mt-3">
                                                <table
                                                    class="table align-middle table-row-dashed table-kemdikti fs-7 gy-2 mb-0"
                                                    id="table_f502_wiraswasta">
                                                    <thead>
                                                        <tr class="fw-bolder text-white">
                                                            <th class="ps-3">Label</th>
                                                            <th class="text-end">Jumlah</th>
                                                            <th class="pe-3 text-end">Persentase</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="fw-bold text-gray-700" id="tbody_f502_wiraswasta">
                                                        @if (isset($statsData['waktu_tunggu_wiraswasta']['table']))
                                                            @foreach ($statsData['waktu_tunggu_wiraswasta']['table'] as $row)
                                                                <tr>
                                                                    <td class="ps-3 text-gray-800">{{ $row['label'] }}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ number_format($row['jumlah'], 0, ',', '.') }}
                                                                        responden</td>
                                                                    <td class="pe-3 text-end">{{ $row['persentase'] }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card kemdikti-card mb-8 shadow-sm">
                                <div
                                    class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="card-label fw-bolder fs-4 text-gray-900 mb-1">
                                            [F4] Bagaimana anda mencari pekerjaan tersebut?
                                        </h4>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-gray-500 fs-8 fw-semibold">F4</span>
                                            <span class="badge badge-light text-gray-600 fw-bold fs-8 border">Filter: F8 =
                                                1,
                                                5</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                        <span class="text-gray-900 fw-bolder fs-2" id="f4_total_responden">
                                            {{ number_format($statsData['metode_mencari_kerja']['total_responden'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-6">
                                    <div class="p-4 mb-4 rounded-3 border-start border-4 border-primary bg-light-primary">
                                        <span class="text-gray-700 fs-7 fw-semibold">
                                            <i class="fa-solid fa-circle-info text-primary me-2"></i>
                                            <strong>Multiple-answer:</strong> Satu responden dapat memilih lebih dari satu
                                            opsi,
                                            sehingga total persentase antar opsi dapat melebihi 100%.
                                        </span>
                                    </div>

                                    <div id="chart_metode_cari_kerja" style="min-height: 480px;"></div>

                                    <div class="table-responsive mt-4">
                                        <table class="table align-middle table-row-dashed table-kemdikti fs-7 gy-3 mb-0"
                                            id="table_f4">
                                            <thead>
                                                <tr class="fw-bolder text-white">
                                                    <th class="ps-4">Label</th>
                                                    <th class="text-center" style="width: 100px;">Kode</th>
                                                    <th class="text-end">Jumlah</th>
                                                    <th class="pe-4 text-end">Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-bold text-gray-700" id="tbody_f4">
                                                @if (isset($statsData['metode_mencari_kerja']['table']))
                                                    @foreach ($statsData['metode_mencari_kerja']['table'] as $row)
                                                        <tr>
                                                            <td class="ps-4 text-gray-800">{{ $row['label'] }}</td>
                                                            <td class="text-center text-primary fw-bolder">
                                                                {{ $row['kode'] }}
                                                            </td>
                                                            <td class="text-end">
                                                                {{ number_format($row['jumlah'], 0, ',', '.') }} responden
                                                            </td>
                                                            <td class="pe-4 text-end">{{ $row['persentase'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr class="fw-bolder bg-light">
                                                    <td class="ps-4 text-gray-900">Total Pilihan Dipilih</td>
                                                    <td></td>
                                                    <td class="text-end text-gray-900" id="f4_table_total_count">
                                                        {{ number_format(array_sum(array_column($statsData['metode_mencari_kerja']['table'] ?? [], 'jumlah')), 0, ',', '.') }}
                                                        pilihan
                                                    </td>
                                                    <td class="pe-4 text-end text-gray-900">-</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>

                            <div class="row g-6 mb-8 skala-provinsi-row">
                                <div class="col-xl-5 col-lg-12">
                                    <div class="card shadow-sm border border-dashed border-gray-400 chart-box h-100"
                                        style="border-radius: 1.25rem;">
                                        <div class="card-header border-0 pt-6">
                                            <div class="card-title">
                                                <span class="fs-4 fw-bolder text-gray-900 d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-earth-americas text-primary"></i> Cakupan Skala
                                                    Tempat Kerja (F5D)
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div id="chart_skala_kerja" style="min-height: 320px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-7 col-lg-12">
                                    <div class="card shadow-sm border border-dashed border-gray-400 chart-box h-100"
                                        style="border-radius: 1.25rem;">
                                        <div class="card-header border-0 pt-6">
                                            <div class="card-title">
                                                <span class="fs-4 fw-bolder text-gray-900 d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-map-location-dot text-danger"></i> Sebaran
                                                    Wilayah
                                                    Tempat Bekerja (Provinsi)
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div id="chart_sebaran_provinsi" style="min-height: 320px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card kemdikti-card kemdikti-card-allow-break mb-8 shadow-sm">
                                <div
                                    class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="card-label fw-bolder fs-3 text-gray-900 mb-1">
                                            Kompetensi Dikuasai Saat Lulus (F17A) &amp; Diperlukan Dalam Pekerjaan (F17B)
                                        </h4>
                                        <span class="text-gray-500 fs-8 fw-semibold">F17A &amp; F17B</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-gray-500 fs-8 d-block fw-semibold">Standar Penilaian</span>
                                        <span class="badge badge-light-primary fw-bold fs-7">Skala Likert 1 &ndash;
                                            5</span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-6">
                                    <div class="p-4 mb-5 rounded-3 border-start border-4 border-info bg-light-info">
                                        <div class="text-gray-700 fs-7 fw-semibold">
                                            <strong>Statistik Section / Grup:</strong> Setiap child instrumen pada section
                                            ini
                                            ditampilkan sebagai kelompok kompetensi untuk analisis kesenjangan (gap
                                            analysis)
                                            antara kompetensi saat lulus dengan tuntutan pekerjaan.
                                        </div>
                                    </div>

                                    <div class="mb-8" style="page-break-inside: avoid; break-inside: avoid;">
                                        <h5 class="fw-bolder text-gray-800 mb-3 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-chart-column text-primary"></i> Perbandingan Skor
                                            Rata-rata
                                            11 Aspek Kompetensi
                                        </h5>
                                        <div id="chart_kompetensi_overview" style="min-height: 400px;"></div>
                                    </div>

                                    <hr class="text-gray-300 my-6 d-print-none">

                                    <div
                                        class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 d-print-none">
                                        <h5 class="fw-bolder text-gray-800 mb-0">Rincian Per Aspek Kompetensi:</h5>
                                        <div class="d-flex flex-wrap gap-2" id="aspect_nav_pills">
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary active btn-aspect"
                                                data-aspect="etika">Etika (F1761/62)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="keahlian">Keahlian Berdasarkan Bidang Ilmu (F1763/64)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="bahasa_inggris">Bahasa Inggris (F1765/66)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="teknologi_informasi">Penggunaan Teknologi Informasi
                                                (F1767/68)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="komunikasi">Komunikasi (F1769/70)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="kerjasama">Kerja Sama Tim (F1771/72)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="pengembangan">Pengembangan Diri (F1773/74)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="berpikir_kritis">Berpikir Kritis (F1775/76)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="kreativitas">Kreativitas (F1777/78)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="kewirausahaan">Kewirausahaan (F1779/80)</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-aspect"
                                                data-aspect="adaptasi">Adaptasi (F1781/82)</button>
                                        </div>
                                    </div>

                                    <div class="row g-6 d-print-none" id="aspect_dual_cards">

                                        <div class="col-xl-6 col-lg-12">
                                            <div class="card border rounded-3 p-5 bg-light h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div>
                                                        <h5 class="fw-bolder text-gray-900 mb-0" id="aspect_label_a">Etika
                                                        </h5>
                                                        <span class="badge badge-light-primary fw-bold fs-8 mt-1"
                                                            id="aspect_code_a">F1761 - Saat Lulus</span>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="text-gray-500 fs-8 d-block">Total Responden</span>
                                                        <span class="fw-bolder fs-4 text-gray-900"
                                                            id="aspect_total_a">0</span>
                                                    </div>
                                                </div>
                                                <div id="chart_aspect_a" style="min-height: 260px;"></div>
                                                <div class="table-responsive mt-3">
                                                    <table
                                                        class="table table-row-dashed table-kemdikti align-middle fs-8 gy-2 mb-0"
                                                        id="table_aspect_a">
                                                        <thead>
                                                            <tr class="text-white">
                                                                <th class="ps-2">Label</th>
                                                                <th class="text-center" style="width: 70px;">Nilai</th>
                                                                <th class="text-end">Jumlah</th>
                                                                <th class="pe-2 text-end">Persentase</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbody_aspect_a"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-12">
                                            <div class="card border rounded-3 p-5 bg-light h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div>
                                                        <h5 class="fw-bolder text-gray-900 mb-0" id="aspect_label_b">Etika
                                                        </h5>
                                                        <span class="badge badge-light-danger fw-bold fs-8 mt-1"
                                                            id="aspect_code_b">F1762 - Diperlukan Pekerjaan</span>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="text-gray-500 fs-8 d-block">Total Responden</span>
                                                        <span class="fw-bolder fs-4 text-gray-900"
                                                            id="aspect_total_b">0</span>
                                                    </div>
                                                </div>
                                                <div id="chart_aspect_b" style="min-height: 260px;"></div>
                                                <div class="table-responsive mt-3">
                                                    <table
                                                        class="table table-row-dashed table-kemdikti align-middle fs-8 gy-2 mb-0"
                                                        id="table_aspect_b">
                                                        <thead>
                                                            <tr class="text-white">
                                                                <th class="ps-2">Label</th>
                                                                <th class="text-center" style="width: 70px;">Nilai</th>
                                                                <th class="text-end">Jumlah</th>
                                                                <th class="pe-2 text-end">Persentase</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbody_aspect_b"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-none d-print-block mt-4" id="kompetensi_print_all">
                                        <div class="border-bottom pb-2 mb-4">
                                            <h5 class="fw-bolder text-gray-900 mb-1">
                                                <i class="fa-solid fa-list-check me-2 text-primary"></i>Rincian Lengkap
                                                Evaluasi 11 Aspek Kompetensi
                                            </h5>
                                            <span class="text-gray-600 fs-8">Perbandingan mendalam penguasaan kompetensi
                                                saat
                                                lulus (F17A) dengan kebutuhan tempat kerja (F17B)</span>
                                        </div>

                                        <div class="d-flex flex-column gap-3" id="kompetensi_print_list">
                                            @if (isset($statsData['kompetensi']['details']))
                                                @php
                                                    $aspectLabels = [
                                                        'etika' => 'Etika',
                                                        'keahlian' => 'Keahlian Berdasarkan Bidang Ilmu',
                                                        'bahasa_inggris' => 'Bahasa Inggris',
                                                        'teknologi_informasi' => 'Penggunaan Teknologi Informasi',
                                                        'komunikasi' => 'Komunikasi',
                                                        'kerjasama' => 'Kerja Sama Tim',
                                                        'pengembangan' => 'Pengembangan Diri',
                                                        'berpikir_kritis' => 'Berpikir Kritis',
                                                        'kreativitas' => 'Kreativitas',
                                                        'kewirausahaan' => 'Kewirausahaan',
                                                        'adaptasi' => 'Adaptasi',
                                                    ];
                                                    $scalesText = [
                                                        1 => 'Sangat Rendah',
                                                        2 => 'Rendah',
                                                        3 => 'Netral / Sedang',
                                                        4 => 'Tinggi',
                                                        5 => 'Sangat Tinggi',
                                                    ];
                                                @endphp

                                                @foreach ($aspectLabels as $k => $lbl)
                                                    @php
                                                        $dtA = $statsData['kompetensi']['details'][$k]['a'] ?? null;
                                                        $dtB = $statsData['kompetensi']['details'][$k]['b'] ?? null;
                                                        $avgA = (float) ($dtA['avg_score'] ?? 0);
                                                        $avgB = (float) ($dtB['avg_score'] ?? 0);
                                                        $gap = round($avgA - $avgB, 2);
                                                        $kodeA = $dtA['kode'] ?? 'F17A';
                                                        $kodeB = $dtB['kode'] ?? 'F17B';
                                                        $totA = $dtA['total_responden'] ?? 0;
                                                        $totB = $dtB['total_responden'] ?? 0;
                                                    @endphp
                                                    <div class="kompetensi-aspect-print-card card border border-dashed border-gray-400 p-4 mb-3"
                                                        style="page-break-inside: avoid; break-inside: avoid;">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                                                            <div>
                                                                <span
                                                                    class="fw-bolder fs-6 text-gray-900">{{ $loop->iteration }}.
                                                                    {{ $lbl }}</span>
                                                                <span class="text-gray-500 fs-8 ms-2">({{ $kodeA }}
                                                                    &amp; {{ $kodeB }})</span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-4 text-end">
                                                                <span class="fs-8">Rata-rata Lulus: <strong
                                                                        class="text-primary">{{ number_format($avgA, 2, ',', '.') }}</strong></span>
                                                                <span class="fs-8">Rata-rata Diperlukan: <strong
                                                                        class="text-danger">{{ number_format($avgB, 2, ',', '.') }}</strong></span>
                                                                <span class="fs-8">Gap Kesenjangan:
                                                                    <strong
                                                                        class="{{ $gap >= 0 ? 'text-success' : 'text-danger' }}">
                                                                        {{ ($gap > 0 ? '+' : '') . number_format($gap, 2, ',', '.') }}
                                                                    </strong>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <table
                                                            class="table table-bordered table-sm fs-8 mb-0 align-middle">
                                                            <thead>
                                                                <tr class="text-center text-white"
                                                                    style="background-color: #5b67ec;">
                                                                    <th rowspan="2"
                                                                        class="align-middle text-start ps-3"
                                                                        style="width: 32%;">Skala Penilaian (Likert 1-5)
                                                                    </th>
                                                                    <th colspan="2" class="text-center"
                                                                        style="background-color: #3b82f6;">Saat Lulus
                                                                        ({{ $kodeA }})
                                                                    </th>
                                                                    <th colspan="2" class="text-center"
                                                                        style="background-color: #ef4444;">Diperlukan
                                                                        Pekerjaan
                                                                        ({{ $kodeB }})</th>
                                                                </tr>
                                                                <tr class="text-center text-white"
                                                                    style="font-size: 8.5px;">
                                                                    <th style="background-color: #2563eb; width: 17%;">
                                                                        Jumlah
                                                                    </th>
                                                                    <th style="background-color: #2563eb; width: 17%;">
                                                                        Persentase</th>
                                                                    <th style="background-color: #dc2626; width: 17%;">
                                                                        Jumlah
                                                                    </th>
                                                                    <th style="background-color: #dc2626; width: 17%;">
                                                                        Persentase</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @for ($s = 1; $s <= 5; $s++)
                                                                    @php
                                                                        $rowA = $dtA['table'][$s - 1] ?? [
                                                                            'label' => $scalesText[$s],
                                                                            'jumlah' => 0,
                                                                            'persentase' => '0,00%',
                                                                        ];
                                                                        $rowB = $dtB['table'][$s - 1] ?? [
                                                                            'label' => $scalesText[$s],
                                                                            'jumlah' => 0,
                                                                            'persentase' => '0,00%',
                                                                        ];
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="ps-3 fw-semibold text-gray-800">
                                                                            {{ $s }}. {{ $scalesText[$s] }}
                                                                        </td>
                                                                        <td class="text-center">
                                                                            {{ number_format($rowA['jumlah']) }} responden
                                                                        </td>
                                                                        <td class="text-center fw-bold">
                                                                            {{ $rowA['persentase'] }}</td>
                                                                        <td class="text-center">
                                                                            {{ number_format($rowB['jumlah']) }} responden
                                                                        </td>
                                                                        <td class="text-center fw-bold">
                                                                            {{ $rowB['persentase'] }}</td>
                                                                    </tr>
                                                                @endfor
                                                                <tr class="fw-bolder bg-light">
                                                                    <td class="ps-3 text-gray-900">Total</td>
                                                                    <td class="text-center text-primary">
                                                                        {{ number_format($totA) }} responden</td>
                                                                    <td class="text-center text-primary">100%</td>
                                                                    <td class="text-center text-danger">
                                                                        {{ number_format($totB) }} responden</td>
                                                                    <td class="text-center text-danger">100%</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row g-6 mb-8">

                                <div class="col-xl-6 col-lg-12">
                                    <div class="card kemdikti-card shadow-sm mb-4 h-100">
                                        <div
                                            class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                            <div>
                                                <h4 class="card-label fw-bolder fs-4 text-gray-900 mb-1">
                                                    Keselarasan Horizontal
                                                </h4>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-gray-500 fs-8 fw-semibold">F14</span>
                                                    <span
                                                        class="badge badge-light text-gray-600 fw-bold fs-8 border">Filter:
                                                        F8 = 1</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                                <span class="text-gray-900 fw-bolder fs-2" id="f14_total_responden">
                                                    {{ number_format($statsData['keselarasan_horizontal']['total_responden'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body pt-2 pb-6">
                                            <div id="chart_keselarasan_horizontal" style="min-height: 290px;"></div>

                                            <div class="table-responsive mt-4">
                                                <table
                                                    class="table align-middle table-row-dashed table-kemdikti fs-7 gy-2 mb-0"
                                                    id="table_f14">
                                                    <thead>
                                                        <tr class="fw-bolder text-white">
                                                            <th class="ps-3">Label</th>
                                                            <th class="text-end">Jumlah</th>
                                                            <th class="pe-3 text-end">Persentase</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="fw-bold text-gray-700" id="tbody_f14">
                                                        @if (isset($statsData['keselarasan_horizontal']['table']))
                                                            @foreach ($statsData['keselarasan_horizontal']['table'] as $row)
                                                                <tr>
                                                                    <td class="ps-3 text-gray-800">{{ $row['label'] }}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ number_format($row['jumlah'], 0, ',', '.') }}
                                                                        responden</td>
                                                                    <td class="pe-3 text-end">{{ $row['persentase'] }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        <tr class="fw-bolder bg-light">
                                                            <td class="ps-3 text-gray-900">Total</td>
                                                            <td class="text-end text-gray-900" id="f14_table_total_count">
                                                                {{ number_format($statsData['keselarasan_horizontal']['total_responden'] ?? 0, 0, ',', '.') }}
                                                                responden
                                                            </td>
                                                            <td class="pe-3 text-end text-gray-900">100%</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-lg-12">
                                    <div class="card kemdikti-card shadow-sm mb-4 h-100">
                                        <div
                                            class="card-header border-0 pt-6 pb-2 d-flex align-items-center justify-content-between">
                                            <div>
                                                <h4 class="card-label fw-bolder fs-4 text-gray-900 mb-1">
                                                    Keselarasan Vertikal
                                                </h4>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-gray-500 fs-8 fw-semibold">F15</span>
                                                    <span
                                                        class="badge badge-light text-gray-600 fw-bold fs-8 border">Filter:
                                                        F8 = 1</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-gray-500 fs-8 d-block fw-semibold">Total Responden</span>
                                                <span class="text-gray-900 fw-bolder fs-2" id="f15_total_responden">
                                                    {{ number_format($statsData['keselarasan_vertikal']['total_responden'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body pt-2 pb-6">
                                            <div id="chart_keselarasan_vertikal" style="min-height: 290px;"></div>

                                            <div class="table-responsive mt-4">
                                                <table
                                                    class="table align-middle table-row-dashed table-kemdikti fs-7 gy-2 mb-0"
                                                    id="table_f15">
                                                    <thead>
                                                        <tr class="fw-bolder text-white">
                                                            <th class="ps-3">Label</th>
                                                            <th class="text-end">Jumlah</th>
                                                            <th class="pe-3 text-end">Persentase</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="fw-bold text-gray-700" id="tbody_f15">
                                                        @if (isset($statsData['keselarasan_vertikal']['table']))
                                                            @foreach ($statsData['keselarasan_vertikal']['table'] as $row)
                                                                <tr>
                                                                    <td class="ps-3 text-gray-800">{{ $row['label'] }}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ number_format($row['jumlah'], 0, ',', '.') }}
                                                                        responden</td>
                                                                    <td class="pe-3 text-end">{{ $row['persentase'] }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        <tr class="fw-bolder bg-light">
                                                            <td class="ps-3 text-gray-900">Total</td>
                                                            <td class="text-end text-gray-900" id="f15_table_total_count">
                                                                {{ number_format($statsData['keselarasan_vertikal']['total_responden'] ?? 0, 0, ',', '.') }}
                                                                responden
                                                            </td>
                                                            <td class="pe-3 text-end text-gray-900">100%</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm border border-dashed border-gray-400 mb-8"
                                style="border-radius: 1.25rem;">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title d-print-none">
                                        <div class="d-flex align-items-center position-relative my-1">
                                            <i
                                                class="fa-solid fa-magnifying-glass fs-3 position-absolute ms-5 text-gray-500"></i>
                                            <input type="text" id="search_rekap_prodi"
                                                class="form-control form-control-solid w-250px ps-13"
                                                placeholder="Cari program studi...">
                                        </div>
                                    </div>
                                    <div class="d-none d-print-block">
                                        <h4 class="fw-bolder text-gray-900 mb-0">Tabel Rekapitulasi Program Studi</h4>
                                    </div>
                                    <div class="card-toolbar">
                                        <span class="badge badge-light-primary fw-bolder fs-7 px-3 py-2">
                                            <i class="fa-solid fa-table me-1"></i> Data Akreditasi Program Studi
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-7 gy-4 mb-0"
                                            id="table_rekap_prodi">
                                            <thead>
                                                <tr class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th class="ps-4" style="width: 40px;">No</th>
                                                    <th>Program Studi</th>
                                                    <th class="d-none d-md-table-cell">Fakultas</th>
                                                    <th class="text-center">Target</th>
                                                    <th class="text-center">Responden</th>
                                                    <th class="text-center" style="width: 120px;">Response Rate</th>
                                                    <th class="text-center text-success">Bekerja</th>
                                                    <th class="text-center text-info">Wirausaha</th>
                                                    <th class="text-center text-primary">Studi Lanjut</th>
                                                    <th class="text-center text-danger">Mencari Kerja</th>
                                                    <th class="pe-4 text-center">Keselarasan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-bold" id="tbody_rekap_prodi">
                                                @forelse ($statsData['rekap_prodi'] as $idx => $r)
                                                    <tr>
                                                        <td class="ps-4 text-center text-gray-500">{{ $idx + 1 }}
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="text-gray-900 fw-bolder fs-6 d-block">{{ $r['nama_prodi'] }}</span>
                                                            <span
                                                                class="badge badge-light fw-bold fs-8">{{ $r['jenjang'] ?? 'S1' }}</span>
                                                        </td>
                                                        <td class="d-none d-md-table-cell text-gray-600">
                                                            {{ $r['fakultas'] }}
                                                        </td>
                                                        <td class="text-center text-gray-800">
                                                            {{ number_format($r['target']) }}</td>
                                                        <td class="text-center text-primary fw-bolder">
                                                            {{ number_format($r['responden']) }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex flex-column align-items-center">
                                                                <span
                                                                    class="fw-bolder fs-7 {{ $r['rate'] >= 50 ? 'text-success' : ($r['rate'] >= 20 ? 'text-warning' : 'text-gray-700') }}">
                                                                    {{ $r['rate'] }}%
                                                                </span>
                                                                <div class="progress h-4px w-80px bg-light mt-1">
                                                                    <div class="progress-bar {{ $r['rate'] >= 50 ? 'bg-success' : ($r['rate'] >= 20 ? 'bg-warning' : 'bg-primary') }}"
                                                                        style="width: {{ min(100, $r['rate']) }}%"></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center text-success fw-bolder">
                                                            {{ $r['bekerja'] }}
                                                        </td>
                                                        <td class="text-center text-info fw-bolder">{{ $r['wirausaha'] }}
                                                        </td>
                                                        <td class="text-center text-primary fw-bolder">
                                                            {{ $r['studi'] }}
                                                        </td>
                                                        <td class="text-center text-danger fw-bolder">{{ $r['mencari'] }}
                                                        </td>
                                                        <td class="pe-4 text-center">
                                                            <span
                                                                class="badge {{ $r['relevan_pct'] >= 70 ? 'badge-light-success' : ($r['relevan_pct'] >= 40 ? 'badge-light-warning' : 'badge-light-secondary') }} fw-bolder fs-8">
                                                                {{ $r['relevan_pct'] }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="11" class="text-center text-gray-500 py-8">
                                                            Tidak ada data program studi
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

                </div>
            </div>

            @include('layouts.footer')
        </div>
    </div>
@endsection

@section('js')
    @include('admin.statistik.script')
@endsection
