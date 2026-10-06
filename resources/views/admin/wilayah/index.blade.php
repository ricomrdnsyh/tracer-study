@extends('layouts.main')

@section('title', 'Master Wilayah')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/buttons.dataTables.min.css') }}">
    <style>
        .table-row-dashed tr {
            border-bottom: 1px dashed #cccccc !important;
        }

        #table-negara thead tr th,
        #table-provinsi thead tr th,
        #table-kabupaten thead tr th {
            vertical-align: middle;
            border-bottom: 1px dashed #cccccc !important;
        }

        #table-negara th,
        #table-negara td,
        #table-provinsi th,
        #table-provinsi td,
        #table-kabupaten th,
        #table-kabupaten td {
            vertical-align: middle !important;
        }

        #table-negara td.dt-control:before,
        #table-negara th.dt-control:before,
        #table-provinsi td.dt-control:before,
        #table-provinsi th.dt-control:before,
        #table-kabupaten td.dt-control:before,
        #table-kabupaten th.dt-control:before {
            display: none !important;
            content: "" !important;
        }

        #table-negara.dataTable td.dt-control,
        #table-negara.dataTable th.dt-control,
        #table-provinsi.dataTable td.dt-control,
        #table-provinsi.dataTable th.dt-control,
        #table-kabupaten.dataTable td.dt-control,
        #table-kabupaten.dataTable th.dt-control {
            position: relative !important;
            width: 28px !important;
            min-width: 28px !important;
            padding: 0 !important;
            text-align: center !important;
            vertical-align: middle !important;
        }

        #table-negara.dataTable.collapsed tbody tr:not(.child) td.dt-control:before,
        #table-negara.dataTable.collapsed tbody tr:not(.child) th.dt-control:before,
        #table-provinsi.dataTable.collapsed tbody tr:not(.child) td.dt-control:before,
        #table-provinsi.dataTable.collapsed tbody tr:not(.child) th.dt-control:before,
        #table-kabupaten.dataTable.collapsed tbody tr:not(.child) td.dt-control:before,
        #table-kabupaten.dataTable.collapsed tbody tr:not(.child) th.dt-control:before {
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

        #table-negara.dataTable.collapsed tbody tr.parent td.dt-control:before,
        #table-negara.dataTable.collapsed tbody tr.parent th.dt-control:before,
        #table-provinsi.dataTable.collapsed tbody tr.parent td.dt-control:before,
        #table-provinsi.dataTable.collapsed tbody tr.parent th.dt-control:before,
        #table-kabupaten.dataTable.collapsed tbody tr.parent td.dt-control:before,
        #table-kabupaten.dataTable.collapsed tbody tr.parent th.dt-control:before {
            content: "–" !important;
            background: #dc3545 !important;
        }

        #table-negara.dataTable td:nth-child(2),
        #table-negara.dataTable th:nth-child(2),
        #table-provinsi.dataTable td:nth-child(2),
        #table-provinsi.dataTable th:nth-child(2),
        #table-kabupaten.dataTable td:nth-child(2),
        #table-kabupaten.dataTable th:nth-child(2) {
            padding-left: 1rem !important;
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
                                        <span class="card-label fw-bolder fs-3 mb-1">Master Wilayah</span>
                                        <span class="text-muted fw-semibold fs-7">Daftar Negara, Provinsi, dan
                                            Kabupaten/Kota</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_import">
                                    <i class="fa-solid fa-file-excel me-2"></i> Import Excel
                                </button>
                            </div>
                        </div>
                        <div class="separator my-5"></div>
                        <div class="card-body pt-0">

                            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x nav-justified fs-5 fw-bold mb-8"
                                style="border-bottom: 1px dashed #cccccc;">
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary justify-content-center active pb-4"
                                        data-bs-toggle="tab" href="#kt_tab_negara">
                                        <i class="fa-solid fa-earth-americas me-2"></i> Negara
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-success justify-content-center pb-4" data-bs-toggle="tab"
                                        href="#kt_tab_provinsi">
                                        <i class="fa-solid fa-map me-2"></i> Provinsi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-warning justify-content-center pb-4" data-bs-toggle="tab"
                                        href="#kt_tab_kabupaten">
                                        <i class="fa-solid fa-city me-2"></i> Kabupaten/Kota
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="kt_tab_negara" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5 w-100"
                                            id="table-negara">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th class="text-center p-0 all" data-priority="1"
                                                        style="width:28px; min-width:28px;"></th>
                                                    <th class="min-w-100px">Kode Negara</th>
                                                    <th class="min-w-200px">Nama Negara</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-bold text-gray-800"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="kt_tab_provinsi" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5 w-100"
                                            id="table-provinsi">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th class="text-center p-0 all" data-priority="1"
                                                        style="width:28px; min-width:28px;"></th>
                                                    <th class="min-w-100px">Kode Provinsi</th>
                                                    <th class="min-w-200px">Provinsi</th>
                                                    <th class="min-w-150px">Negara</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-bold text-gray-800"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="kt_tab_kabupaten" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5 w-100"
                                            id="table-kabupaten">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th class="text-center p-0 all" data-priority="1"
                                                        style="width:28px; min-width:28px;"></th>
                                                    <th class="min-w-100px">Kode Kab/Kota</th>
                                                    <th class="min-w-200px">Kabupaten/Kota</th>
                                                    <th class="min-w-150px">Provinsi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-bold text-gray-800"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.wilayah.import')

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

    @include('admin.wilayah.script.index')
@endsection
