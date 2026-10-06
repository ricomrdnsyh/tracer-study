@extends('layouts.main')

@section('title', 'Detail PT / Instansi Alumni')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/buttons.dataTables.min.css') }}">
    <style>
        .table-row-dashed tr {
            border-bottom: 1px dashed #cccccc !important;
        }

        #example thead tr th {
            vertical-align: middle;
            border-bottom: 1px dashed #cccccc !important;
        }

        #example th,
        #example td {
            vertical-align: middle !important;
        }

        #example td.dt-control:before,
        #example th.dt-control:before {
            display: none !important;
            content: "" !important;
        }

        #example.dataTable td.dt-control,
        #example.dataTable th.dt-control {
            position: relative !important;
            width: 28px !important;
            min-width: 28px !important;
            padding: 0 !important;
            text-align: center !important;
            vertical-align: middle !important;
        }

        #example.dataTable.collapsed tbody tr:not(.child) td.dt-control:before,
        #example.dataTable.collapsed tbody tr:not(.child) th.dt-control:before {
            display: inline-flex !important;
            content: "+" !important;
            position: absolute !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, calc(-50% + 7px)) !important;
            width: 18px !important;
            height: 18px !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 999px !important;
            color: #fff !important;
            font-weight: 900 !important;
            font-size: 13px !important;
            line-height: 1 !important;
            background: #0d6efd !important;
            box-shadow: 0 0 0 2px #ffffff, 0 2px 6px rgba(0, 0, 0, .18) !important;
        }

        #example.dataTable.collapsed tbody tr.parent td.dt-control:before,
        #example.dataTable.collapsed tbody tr.parent th.dt-control:before {
            content: "–" !important;
            background: #dc3545 !important;
        }

        #example.dataTable td:nth-child(2),
        #example.dataTable th:nth-child(2) {
            padding-left: .25rem !important;
        }

        #example .action-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .5rem .6rem;
            background: #f5f8fa;
            border-radius: .5rem;
            white-space: nowrap;
        }

        #example .action-wrap .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        #example .action-wrap i {
            line-height: 1 !important;
        }
    </style>
@endsection

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">

            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-8">
                        <div>
                            <h3 class="fw-bolder fs-1 text-dark m-0">{{ $nama }}</h3>
                            <span class="text-gray-500 fw-semibold fs-6 mt-1 d-block">Detail PT / Instansi dan daftar alumni
                                yang terhubung</span>
                        </div>
                        <div class="mt-4 mt-md-0">
                            <a href="{{ route('admin.perusahaan.index') }}"
                                class="btn btn-sm btn-light-primary fw-bolder shadow-sm">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="row g-5 g-xl-8 mb-8">
                        <div class="col-xl-8">
                            <div class="card shadow-sm border border-dashed border-dark rounded-4 h-100">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder text-dark fs-4">Profil Instansi</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-4">
                                    <div class="d-flex align-items-center mb-8">
                                        <div class="symbol symbol-60px symbol-circle me-5">
                                            <span
                                                class="symbol-label bg-light-primary text-primary fs-2 fw-bolder">{{ strtoupper(substr($nama, 0, 1)) }}</span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-dark fw-bolder fs-3">{{ $nama }}</span>
                                            <span class="text-gray-500 fw-semibold fs-6">Terdaftar di Sistem Tracer
                                                Study</span>
                                        </div>
                                    </div>

                                    <div class="row g-5">
                                        <div class="col-md-6">
                                            <div class="border border-info border-dashed rounded p-5 h-100 bg-light-info d-flex align-items-center">
                                                <div class="symbol symbol-45px me-4">
                                                    <div class="symbol-label bg-info shadow-sm d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-building text-white fs-5"></i>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-info fw-bold fs-7 mb-1">Jenis Instansi</span>
                                                    <span class="text-gray-800 fw-bolder fs-5">{{ $perusahaan->jenis_instansi ?: '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div
                                                class="border border-success border-dashed rounded p-5 h-100 bg-light-success d-flex align-items-center">
                                                <div class="symbol symbol-45px me-4">
                                                    <div class="symbol-label bg-success shadow-sm d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-map-marker-alt text-white fs-5"></i>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-success fw-bold fs-7 mb-1">Lokasi</span>
                                                    <span class="text-gray-800 fw-bolder fs-5">
                                                        @php
                                                            $loc = [];
                                                            if ($perusahaan->kabupaten) {
                                                                $loc[] = $perusahaan->kabupaten;
                                                            }
                                                            if ($perusahaan->provinsi) {
                                                                $loc[] = $perusahaan->provinsi;
                                                            }
                                                            echo count($loc) > 0 ? implode(', ', $loc) : '-';
                                                        @endphp
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div
                                class="card shadow-sm border border-dashed border-primary rounded-4 h-100 bg-light-primary">
                                <div
                                    class="card-body d-flex flex-column justify-content-center align-items-center text-center p-8">
                                    <i class="fas fa-users text-primary fs-4x mb-4 opacity-75"></i>
                                    <div class="text-primary fw-bolder fs-4x mb-2" style="line-height: 1;">
                                        {{ $pekerjaanList->count() }}</div>
                                    <div class="text-primary fw-bold fs-5">Alumni Terhubung</div>
                                    <div class="text-primary opacity-75 mt-4 fs-7 px-4">
                                        Jumlah mahasiswa/alumni yang bekerja di instansi ini berdasarkan respons tracer
                                        study.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border border-dashed border-dark rounded">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder fs-3 mb-1">Daftar Mahasiswa Alumni</span>
                                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Mahasiswa yang terhubung ke PT / Instansi ini</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="card-toolbar">
                            </div>
                        </div>
                        <div class="separator my-5"></div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 w-100" id="example">
                                    <thead class="">
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="text-center p-0" style="width:28px; min-width:28px;"></th>
                                            <th class="text-center ps-1 min-w-100px">Aksi</th>
                                            <th class="min-w-150px">NIM</th>
                                            <th class="min-w-200px">Nama Mahasiswa</th>
                                            <th class="min-w-150px">Fakultas</th>
                                            <th class="min-w-150px">Program Studi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-bold text-gray-800">
                                        @forelse($pekerjaanList as $p)
                                            @php
                                                $mhs = $p->responTracer->mahasiswa ?? null;
                                                $prodi = $mhs->prodi ?? null;
                                                $fakultas = $prodi->fakultas ?? null;
                                                $nama_mhs = $mhs ? $mhs->nama : '-';
                                            @endphp
                                            <tr>
                                                <td class="text-center"></td>
                                                <td class="text-center">
                                                    @if ($p->responTracer)
                                                        <a href="{{ route('admin.respon.show', $p->responTracer->id_respon) }}"
                                                            class="btn btn-sm btn-light btn-active-light-info text-center"
                                                            data-bs-toggle="tooltip" title="Lihat Detail Jawaban">
                                                            <i class="fas fa-eye"></i> Detail
                                                        </a>
                                                    @else
                                                        <span class="text-muted fs-7 fst-italic">Data tidak tersedia</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-gray-800 fw-bold">{{ $mhs ? $mhs->nim : '-' }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-gray-800 fw-bold">{{ $nama_mhs }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-gray-800 fw-bold">{{ $fakultas ? $fakultas->nama_fakultas : '-' }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-gray-800 fw-bold">{{ $prodi ? $prodi->nama_prodi : '-' }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-8">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="fas fa-box-open fs-2x text-muted mb-3"></i>
                                                        <span class="fw-semibold fs-6">Belum ada alumni yang
                                                            terhubung</span>
                                                    </div>
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
    <script src="{{ asset('assets/plugins/custom/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/lodash.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/dataTables.colReorder.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/dataTables.buttons.min.js') }}"></script>

    <script src="{{ asset('assets/plugins/custom/datatables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/print.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/responsive.bootstrap.min.js') }}"></script>

    @include('admin.perusahaan.script.show')
@endsection
