@extends('layouts.main')

@section('title', 'Statistik Tracer Study')

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
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
        }

        .chart-box {
            border-radius: 1rem;
            min-height: 360px;
        }

        .chart-empty-state {
            min-height: 280px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #a1a5b7;
        }

        .table-row-dashed tr {
            border-bottom: 1px dashed #e4e6ef !important;
        }

        [data-bs-theme="dark"] .table-row-dashed tr {
            border-bottom: 1px dashed #2b2b40 !important;
        }

        .table-rekap th {
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            border-radius: 1rem;
        }

        [data-bs-theme="dark"] .loading-overlay {
            background: rgba(30, 30, 45, 0.75);
        }

        @media print {
            #kt_app_sidebar,
            #kt_app_header,
            #kt_app_footer,
            .no-print,
            .filter-container {
                display: none !important;
            }
            .app-main {
                margin: 0 !important;
                padding: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                page-break-inside: avoid;
            }
        }
    </style>
@endsection

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">

            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    <!-- Header Section -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mb-6">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-light-primary fw-bold px-3 py-2">
                                    <i class="fa-solid fa-chart-line text-primary me-1"></i> Executive Analytics
                                </span>
                            </div>
                            <h1 class="text-gray-900 fw-bolder fs-1 mb-1 mt-2">Statistik Tracer Study</h1>
                            <span class="text-gray-600 fw-semibold fs-6">
                                Analisis data partisipasi alumni, relevansi kurikulum, masa tunggu, dan ketenagakerjaan berstandar Dikti
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 no-print">
                            <button type="button" class="btn btn-sm btn-light-primary fw-bold" id="btn_refresh_stats">
                                <i class="fa-solid fa-rotate-right me-2"></i>Muat Ulang
                            </button>
                            <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="window.print();">
                                <i class="fa-solid fa-print me-2"></i>Cetak / Ekspor PDF
                            </button>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="card shadow-sm border border-dashed border-gray-400 mb-8 filter-container" style="border-radius: 1.25rem;">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="text-primary fw-bolder mb-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-filter text-primary"></i> Filter Analitik
                                </h5>
                                <button type="button" class="btn btn-link btn-color-muted btn-active-color-primary p-0 fs-7 fw-bold" id="btn_reset_filter">
                                    <i class="fa-solid fa-arrows-rotate fs-8 me-1"></i> Reset Filter
                                </button>
                            </div>

                            <div class="row g-4">
                                <!-- Filter Kuesioner -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold fs-7 mb-2 text-gray-700">Kuesioner Tracer:</label>
                                    <select id="filter_kuesioner" class="form-select form-select-sm" data-control="select2"
                                        data-placeholder="Semua Kuesioner" data-allow-clear="true">
                                        <option value="all">Semua Kuesioner</option>
                                        @foreach ($kuesionerList as $k)
                                            <option value="{{ $k->id_kuesioner }}">{{ $k->judul }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filter Tahun Akademik Kelulusan -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold fs-7 mb-2 text-gray-700">Tahun Akademik Kelulusan:</label>
                                    <select id="filter_akademik" class="form-select form-select-sm" data-control="select2"
                                        data-placeholder="Semua Tahun Akademik" data-allow-clear="true">
                                        <option value="all">Semua Tahun Akademik</option>
                                        @foreach ($tahunAkademikList as $ta)
                                            <option value="{{ $ta->id_smt }}">{{ $ta->nm_smt }} ({{ $ta->id_smt }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filter Fakultas -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold fs-7 mb-2 text-gray-700">Fakultas:</label>
                                    <select id="filter_fakultas" class="form-select form-select-sm" data-control="select2"
                                        data-placeholder="Semua Fakultas" data-allow-clear="true" {{ $isFakultas ? 'disabled' : '' }}>
                                        <option value="all">Semua Fakultas</option>
                                        @foreach ($fakultasList as $f)
                                            <option value="{{ $f->id_fakultas }}" {{ $isFakultas && $userFakultasId == $f->id_fakultas ? 'selected' : '' }}>
                                                {{ $f->nama_fakultas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filter Program Studi -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold fs-7 mb-2 text-gray-700">Program Studi:</label>
                                    <select id="filter_prodi" class="form-select form-select-sm" data-control="select2"
                                        data-placeholder="Semua Program Studi" data-allow-clear="true">
                                        <option value="all">Semua Program Studi</option>
                                        @foreach ($prodiList as $p)
                                            <option value="{{ $p->id_prodi }}" data-fakultas="{{ $p->fakultas_id }}">
                                                {{ $p->nama_prodi }} ({{ $p->jenjang ?? 'S1' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Metric Cards -->
                    <div class="row g-5 g-xl-6 mb-8 position-relative">
                        <div class="loading-overlay" id="kpi_loading">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>

                        <!-- Card 1: Total Alumni Target -->
                        <div class="col-xl col-md-6">
                            <div class="card stat-card-modern p-5 h-100 border-primary bg-light-primary">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="text-gray-600 fw-bold fs-7">Total Alumni</span>
                                    <div class="stat-icon-wrapper bg-white shadow-sm text-primary">
                                        <i class="fa-solid fa-graduation-cap fs-3"></i>
                                    </div>
                                </div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="text-gray-900 fw-bolder fs-2x" id="kpi_total_alumni">
                                        {{ number_format($statsData['kpi']['total_alumni'] ?? 0) }}
                                    </div>
                                    <span class="text-gray-500 fs-7 fw-semibold">Target</span>
                                </div>
                                <div class="text-gray-500 fs-8 mt-1">Populasi kelulusan terdata</div>
                            </div>
                        </div>

                        <!-- Card 2: Total Responden -->
                        <div class="col-xl col-md-6">
                            <div class="card stat-card-modern p-5 h-100 border-success bg-light-success">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="text-gray-600 fw-bold fs-7">Total Responden</span>
                                    <div class="stat-icon-wrapper bg-white shadow-sm text-success">
                                        <i class="fa-solid fa-user-check fs-3"></i>
                                    </div>
                                </div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="text-gray-900 fw-bolder fs-2x" id="kpi_total_responden">
                                        {{ number_format($statsData['kpi']['total_responden'] ?? 0) }}
                                    </div>
                                    <span class="text-gray-500 fs-7 fw-semibold">Selesai</span>
                                </div>
                                <div class="text-gray-500 fs-8 mt-1">Alumni mengisi kuesioner</div>
                            </div>
                        </div>

                        <!-- Card 3: Response Rate -->
                        <div class="col-xl col-md-6">
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
                                        style="width: {{ min(100, $statsData['kpi']['response_rate'] ?? 0) }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Keselarasan Kerja Relevan -->
                        <div class="col-xl col-md-6">
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

                        <!-- Card 5: Rata-rata Waktu Tunggu -->
                        <div class="col-xl col-md-6">
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

                    <!-- Row 1 Charts: Status Aktivitas & Keselarasan Bidang Studi -->
                    <div class="row g-6 mb-8">
                        <div class="col-xl-5 col-lg-12">
                            <div class="card shadow-sm border border-dashed border-gray-400 chart-box h-100">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <span class="fs-4 fw-bolder text-gray-900 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-briefcase text-primary"></i> Status Aktivitas Alumni (F8)
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="chart_status_aktivitas" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-7 col-lg-12">
                            <div class="card shadow-sm border border-dashed border-gray-400 chart-box h-100">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <span class="fs-4 fw-bolder text-gray-900 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-layer-group text-success"></i> Relevansi Bidang Studi & Pekerjaan (F14)
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="chart_keselarasan" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 Charts: Waktu Tunggu & Kesesuaian Jenjang Pendidikan -->
                    <div class="row g-6 mb-8">
                        <div class="col-xl-6 col-lg-12">
                            <div class="card shadow-sm border border-dashed border-gray-400 chart-box h-100">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <span class="fs-4 fw-bolder text-gray-900 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-clock text-info"></i> Waktu Tunggu Memperoleh Pekerjaan Pertama (F502)
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="chart_waktu_tunggu" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-12">
                            <div class="card shadow-sm border border-dashed border-gray-400 chart-box h-100">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <span class="fs-4 fw-bolder text-gray-900 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-graduation-cap text-warning"></i> Kesesuaian Jenjang Pendidikan (F15)
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="chart_kesesuaian_jenjang" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3 Charts: Jenis Instansi & Sebaran Wilayah / Provinsi -->
                    <div class="row g-6 mb-8">
                        <div class="col-xl-6 col-lg-12">
                            <div class="card shadow-sm border border-dashed border-gray-400 chart-box h-100">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <span class="fs-4 fw-bolder text-gray-900 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-building text-primary"></i> Jenis Instansi / Tempat Bekerja (F1101)
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="chart_jenis_instansi" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-12">
                            <div class="card shadow-sm border border-dashed border-gray-400 chart-box h-100">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <span class="fs-4 fw-bolder text-gray-900 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-map-location-dot text-danger"></i> Sebaran Wilayah Tempat Bekerja (Provinsi)
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="chart_sebaran_provinsi" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Tabel Rekapitulasi per Program Studi -->
                    <div class="card shadow-sm border border-dashed border-dark mb-8" style="border-radius: 1.25rem;">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <div>
                                    <span class="card-label fw-bolder fs-3 text-gray-900 d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-table-list text-primary"></i> Rekapitulasi Partisipasi & Capaian per Program Studi
                                    </span>
                                    <span class="text-gray-600 fw-semibold fs-7 d-block mt-1">
                                        Rincian target, jumlah responden, status aktivitas, dan keselarasan studi alumni per program studi
                                    </span>
                                </div>
                            </div>
                            <div class="card-toolbar no-print">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="fa-solid fa-magnifying-glass fs-5 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" id="search_rekap_prodi" class="form-control form-control-sm form-control-solid ps-12 w-200px" placeholder="Cari Prodi..." />
                                </div>
                            </div>
                        </div>
                        <div class="separator my-2"></div>
                        <div class="card-body pt-2 pb-6">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-7 gy-4 table-rekap" id="table_rekap_prodi">
                                    <thead>
                                        <tr class="text-start text-gray-600 fw-bolder bg-light">
                                            <th class="ps-4 min-w-40px text-center">No</th>
                                            <th class="min-w-180px">Program Studi</th>
                                            <th class="min-w-140px d-none d-md-table-cell">Fakultas</th>
                                            <th class="min-w-90px text-center">Target</th>
                                            <th class="min-w-90px text-center">Responden</th>
                                            <th class="min-w-130px text-center">Response Rate</th>
                                            <th class="min-w-80px text-center text-success">Bekerja</th>
                                            <th class="min-w-80px text-center text-info">Wirausaha</th>
                                            <th class="min-w-80px text-center text-primary">Studi</th>
                                            <th class="min-w-90px text-center text-danger">Mencari Kerja</th>
                                            <th class="pe-4 min-w-110px text-center text-warning">Keselarasan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-bold text-gray-700" id="tbody_rekap_prodi">
                                        @forelse ($statsData['rekap_prodi'] as $idx => $r)
                                            <tr>
                                                <td class="ps-4 text-center text-gray-500">{{ $idx + 1 }}</td>
                                                <td>
                                                    <span class="text-gray-900 fw-bolder fs-6 d-block">{{ $r['nama_prodi'] }}</span>
                                                    <span class="badge badge-light fw-bold fs-8">{{ $r['jenjang'] }}</span>
                                                </td>
                                                <td class="d-none d-md-table-cell text-gray-600">{{ $r['fakultas'] }}</td>
                                                <td class="text-center text-gray-800">{{ number_format($r['target']) }}</td>
                                                <td class="text-center text-primary fw-bolder">{{ number_format($r['responden']) }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <span class="fw-bolder fs-7 {{ $r['rate'] >= 50 ? 'text-success' : ($r['rate'] >= 20 ? 'text-warning' : 'text-gray-700') }}">
                                                            {{ $r['rate'] }}%
                                                        </span>
                                                        <div class="progress h-4px w-80px bg-light mt-1">
                                                            <div class="progress-bar {{ $r['rate'] >= 50 ? 'bg-success' : ($r['rate'] >= 20 ? 'bg-warning' : 'bg-primary') }}"
                                                                style="width: {{ min(100, $r['rate']) }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center text-success fw-bolder">{{ $r['bekerja'] }}</td>
                                                <td class="text-center text-info fw-bolder">{{ $r['wirausaha'] }}</td>
                                                <td class="text-center text-primary fw-bolder">{{ $r['studi'] }}</td>
                                                <td class="text-center text-danger fw-bolder">{{ $r['mencari'] }}</td>
                                                <td class="pe-4 text-center">
                                                    <span class="badge {{ $r['relevan_pct'] >= 70 ? 'badge-light-success' : ($r['relevan_pct'] >= 40 ? 'badge-light-warning' : 'badge-light-secondary') }} fw-bolder fs-8">
                                                        {{ $r['relevan_pct'] }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-gray-500 py-8">
                                                    Belum ada data rekapitulasi program studi
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
    @include('admin.statistik.script')
@endsection
