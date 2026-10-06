@extends('layouts.main')

@section('title', 'Respon Tracer Study')

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

        #example td:nth-child(2),
        #example th:nth-child(2) {
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
                    <div class="card shadow-sm border border-dashed border-dark rounded">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder fs-3 mb-1">Daftar Respon Mahasiswa</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <!-- Tombol toolbar jika ada nantinya -->
                            </div>
                        </div>

                        <div class="card-body py-4 px-8 filter-container mt-4">
                            <div class="border border-dashed rounded p-5 mb-5" style="border-color: #b5b5c3 !important;">
                                <h5 class="text-primary mb-4"><i class="fas fa-filter text-primary me-2"></i>Filter Data
                                </h5>
                                <div class="row g-5">
                                    @if (auth()->user()->role !== 'Fakultas')
                                        <div class="col-lg-4 col-md-12 col-sm-12">
                                            <label class="form-label fw-bold mb-2">Fakultas:</label>
                                            <select id="filter_fakultas" class="form-select form-select-sm"
                                                data-control="select2" data-placeholder="Semua Fakultas"
                                                data-allow-clear="true">
                                                <option value="">Semua Fakultas</option>
                                                @foreach ($fakultas as $f)
                                                    <option value="{{ $f->id_fakultas }}">{{ $f->nama_fakultas }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div
                                        class="{{ auth()->user()->role !== 'Fakultas' ? 'col-lg-4' : 'col-lg-6' }} col-md-12 col-sm-12">
                                        <label class="form-label fw-bold mb-2">Program Studi:</label>
                                        <select id="filter_prodi" class="form-select form-select-sm" data-control="select2"
                                            data-placeholder="Semua Program Studi" data-allow-clear="true">
                                            <option value="">Semua Program Studi</option>
                                            @foreach ($prodi as $p)
                                                <option value="{{ $p->id_prodi }}" data-fakultas="{{ $p->fakultas_id }}">
                                                    {{ $p->nama_prodi }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div
                                        class="{{ auth()->user()->role !== 'Fakultas' ? 'col-lg-4' : 'col-lg-6' }} col-md-12 col-sm-12">
                                        <label class="form-label fw-bold mb-2">Kuesioner:</label>
                                        <select id="filter_kuesioner" class="form-select form-select-sm"
                                            data-control="select2" data-placeholder="Semua Kuesioner"
                                            data-allow-clear="true">
                                            <option value="">Semua Kuesioner</option>
                                            @foreach ($kuesioner as $k)
                                                <option value="{{ $k->id_kuesioner }}">{{ $k->judul }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 w-100" id="example">
                                    <thead class="">
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="text-center p-0" style="width:28px; min-width:28px;"></th>
                                            <th class="text-center ps-1 min-w-100px">Aksi</th>
                                            <th class="min-w-150px">Nama Mahasiswa</th>
                                            <th class="min-w-150px">NIM</th>
                                            <th class="min-w-150px">Judul Kuesioner</th>
                                            <th class="min-w-150px">Tanggal Isi</th>
                                            <th class="min-w-100px">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-bold text-gray-800"></tbody>
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

    @include('admin.respon.script.index')
@endsection
