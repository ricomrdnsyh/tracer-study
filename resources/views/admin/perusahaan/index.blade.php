@extends('layouts.main')

@section('title', 'PT / Instansi')

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
                    
                    <div class="mb-5">
                        <span class="text-gray-600 fw-semibold">Rekap perusahaan atau instansi tempat alumni bekerja berdasarkan data tracer study.</span>
                    </div>

                    <div class="row g-5 g-xl-8 mb-8">
                        <div class="col-xl-6">
                            <div class="card shadow-sm border border-dashed border-dark rounded bg-light-primary">
                                <div class="card-body py-5">
                                    <div class="text-gray-500 fw-bold fs-6 mb-2">TOTAL PT / INSTANSI</div>
                                    <div class="text-dark fw-bolder fs-1">{{ $totalPerusahaan }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card shadow-sm border border-dashed border-dark rounded bg-light-success">
                                <div class="card-body py-5">
                                    <div class="text-gray-500 fw-bold fs-6 mb-2">MAHASISWA TERHUBUNG</div>
                                    <div class="text-dark fw-bolder fs-1">{{ $totalMahasiswa }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border border-dashed border-dark rounded">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title w-100">
                                <div class="d-flex align-items-center position-relative my-1 w-100">
                                    <input type="text" id="search_box" class="form-control form-control-solid w-100" placeholder="Cari nama PT, instansi, atau lokasi..." />
                                </div>
                            </div>
                            <div class="card-toolbar ms-3">
                                <button type="button" class="btn btn-light" id="reset_search">Reset</button>
                            </div>
                        </div>
                        <div class="separator my-5"></div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 w-100" id="example">
                                    <thead class="">
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="min-w-150px">PT / Instansi</th>
                                            <th class="min-w-100px">Jenis</th>
                                            <th class="min-w-150px">Lokasi</th>
                                            <th class="min-w-50px text-center">Mahasiswa</th>
                                            <th class="text-center min-w-100px">Aksi</th>
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

    @include('admin.perusahaan.script.index')
@endsection
